<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Category;
use App\Models\Movement;
use App\Models\MovementItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $operation = $request->input('operation');
        $query = Movement::with(['product', 'warehouse', 'user', 'technician', 'parent', 'transfer.creator', 'transfer.sourceWarehouse', 'transfer.destinationWarehouse'])
            ->whereHas('product', fn ($query) => $query->where('type', 'service'))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->product_id))
            ->when($request->filled('category_id'), fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('category_id', $request->category_id)))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('technician_id'), function ($query) use ($request) {
                $query->where(function ($responsibleQuery) use ($request) {
                    $responsibleQuery->where('technician_id', $request->technician_id)
                        ->orWhereHas('transfer', fn ($transferQuery) => $transferQuery->where('created_by', $request->technician_id));
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->q;
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('reference_code', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('internal_code', 'like', "%{$search}%")
                                ->orWhere('name_item', 'like', "%{$search}%")
                                ->orWhere('mac', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items', function ($itemQuery) use ($search) {
                            $itemQuery->where('serial_number', 'like', "%{$search}%")
                                ->orWhere('mac_address', 'like', "%{$search}%");
                        });
                });
            });

        if ($operation === 'entry') {
            $query->where('type', 'in')->where(function ($builder) {
                $builder->whereNull('reason')->orWhere('reason', 'not like', 'Devolución%');
            });
        } elseif ($operation === 'return') {
            $query->where('type', 'in')->where('reason', 'like', 'Devolución%');
        } elseif ($operation === 'exit') {
            $query->where('type', 'out')->where('movement_kind', 'original');
        } elseif ($operation === 'transfer') {
            $query->whereIn('movement_kind', ['transfer_out', 'transfer_in']);
        } elseif ($operation === 'adjustment') {
            $query->where('reason', 'like', 'Ajuste%');
        } elseif ($operation === 'disposal') {
            $query->where('status', 'disposed');
        }

        return view('movements.index', [
            'movements' => $query->latest()->paginate(20)->withQueryString(),
            'products' => Product::where('type', 'service')->orderBy('name_item')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'technicians' => User::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'transfers' => StockTransfer::with(['product', 'sourceWarehouse', 'destinationWarehouse'])
                ->latest()
                ->paginate(10, ['*'], 'transfers_page'),
        ]);
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $operationMap = [
            'entry' => ['type' => 'in', 'status' => 'completed'],
            'return' => ['type' => 'in', 'status' => 'completed'],
            'exit' => ['type' => 'out', 'status' => 'pending'],
            'adjustment' => ['type' => $data['adjustment_direction'], 'status' => 'completed'],
            'disposal' => ['type' => 'out', 'status' => 'completed'],
        ];

        if ($data['operation_type'] === 'transfer') {
            return redirect()->route('movements.index')->withErrors(['operation_type' => 'Las transferencias se registran desde el formulario de transferencia.']);
        }

        $data['type'] = $operationMap[$data['operation_type']]['type'];
        $data['status'] = $operationMap[$data['operation_type']]['status'];

        if ($data['operation_type'] === 'exit') {
            $data['reason'] = 'Entrega a técnico';
        }

        DB::transaction(function () use ($data) {
            $stock = ProductStock::firstOrCreate(
                [
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                ],
                ['current_stock' => 0]
            );

            if ($data['type'] === 'out' && $stock->current_stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuficiente en el almacén seleccionado.',
                ]);
            }

            $data['type'] === 'in'
                ? $stock->increment('current_stock', $data['quantity'])
                : $stock->decrement('current_stock', $data['quantity']);

            if ($data['operation_type'] === 'disposal') {
                $stock->increment('disposed_stock', $data['quantity']);
            }

            $stock->refresh();
            Product::whereKey($data['product_id'])->update([
                'quantity' => ProductStock::where('product_id', $data['product_id'])->sum('current_stock'),
            ]);

            $movement = Movement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => $data['type'],
                'status' => $data['status'],
                'reason' => $data['reason'] ?? null,
                'origin' => $data['origin'] ?? null,
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
                'user_id' => (int) auth()->id(),
                'technician_id' => $data['technician_id'] ?? null,
                'movement_kind' => 'original',
                'movement_group_id' => (string) Str::uuid(),
            ]);

            $movement->update([
                'reference_code' => sprintf('%s-%06d', $data['type'] === 'out' ? 'SAL' : 'ENT', $movement->id),
            ]);

            if ($data['operation_type'] === 'exit') {
                $items = collect(range(1, (int) $data['quantity']))
                    ->map(fn (int $itemNumber) => ['item_number' => $itemNumber]);
                $movement->items()->createMany($items->all());
            }

            if (in_array($data['operation_type'], ['entry', 'return', 'disposal'], true)) {
                Product::whereKey($data['product_id'])->update([
                    'current_status' => $data['operation_type'] === 'disposal' ? 'disposed' : 'available',
                ]);
            }
        });

        return redirect()
            ->route('movements.index')
            ->with('ok', 'Movimiento registrado correctamente.');
    }

    public function show(Movement $movement): View
    {
        abort_unless($movement->product?->type === 'service', 404);

        if ($movement->type === 'out' && $movement->items()->count() < $movement->quantity) {
            $lastItemNumber = (int) $movement->items()->max('item_number');
            $pendingItems = collect(range($lastItemNumber + 1, (int) $movement->quantity))
                ->map(fn (int $itemNumber) => ['item_number' => $itemNumber]);
            $movement->items()->createMany($pendingItems->all());
        }

        $movement->load(['product', 'warehouse', 'user', 'technician', 'items', 'relatedMovements']);

        return view('movements.show', compact('movement'));
    }

    public function liquidate(Request $request, Movement $movement): RedirectResponse
    {
        abort_unless($movement->type === 'out' && $movement->product?->type === 'service', 404);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.final_status' => ['required', 'in:in_use,returned,damaged,under_repair,lost,disposed,other'],
            'items.*.result_reason' => ['nullable', 'in:customer_damage,technical_failure,installation_damage,transport_damage,other'],
            'items.*.serial_number' => ['nullable', 'string', 'max:120'],
            'items.*.mac_address' => ['nullable', 'string', 'max:50'],
            'items.*.internal_code' => ['nullable', 'string', 'max:100'],
            'items.*.patrimonial_code' => ['nullable', 'string', 'max:100'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['items'] as $itemId => $itemData) {
            if (in_array($itemData['final_status'], ['damaged', 'other'], true) && empty($itemData['result_reason'])) {
                throw ValidationException::withMessages([
                    "items.{$itemId}.result_reason" => 'Selecciona un motivo para este resultado.',
                ]);
            }
        }

        DB::transaction(function () use ($movement, $validated): void {
            $movement->load('items');

            foreach ($movement->items as $item) {
                if ($item->liquidation_status !== 'pending') {
                    continue;
                }

                $data = $validated['items'][$item->id] ?? null;
                if (! $data) {
                    continue;
                }

                if ($data['final_status'] === 'returned') {
                    $stock = ProductStock::updateOrCreate(
                        [
                            'product_id' => $movement->product_id,
                            'warehouse_id' => $movement->warehouse_id,
                        ],
                        ['current_stock' => 0]
                    );
                    $stock->increment('current_stock');
                    $stock->refresh();
                    Product::whereKey($movement->product_id)->update([
                        'quantity' => ProductStock::where('product_id', $movement->product_id)->sum('current_stock'),
                    ]);
                } else {
                    $stockColumn = [
                        'in_use' => 'in_use_stock',
                        'reserved' => 'reserved_stock',
                        'under_repair' => 'repair_stock',
                        'damaged' => 'damaged_stock',
                        'lost' => 'lost_stock',
                        'disposed' => 'disposed_stock',
                        'other' => 'other_stock',
                    ][$data['final_status']];

                    ProductStock::updateOrCreate(
                        [
                            'product_id' => $movement->product_id,
                            'warehouse_id' => $movement->warehouse_id,
                        ],
                        ['current_stock' => 0]
                    )->increment($stockColumn);
                }

                $item->update([
                    'serial_number' => $data['serial_number'] ?? null,
                    'mac_address' => $data['mac_address'] ?? null,
                    'internal_code' => $data['internal_code'] ?? null,
                    'patrimonial_code' => $data['patrimonial_code'] ?? null,
                    'final_status' => $data['final_status'],
                    'result_reason' => $data['result_reason'] ?? null,
                    'liquidation_status' => 'liquidated',
                    'note' => $data['note'] ?? null,
                ]);
            }

            $pendingItems = $movement->items()->where('liquidation_status', 'pending')->count();
            $movement->update([
                'status' => $pendingItems === 0 ? 'completed' : 'partially_liquidated',
            ]);
            if ($pendingItems === 0 && ! $movement->relatedMovements()->where('movement_kind', 'liquidation')->exists()) {
                $summary = $movement->items()
                    ->whereNotNull('final_status')
                    ->get()
                    ->groupBy('final_status')
                    ->map(fn ($items) => $items->count())
                    ->map(fn ($count, $status) => "{$count} {$status}")
                    ->values()
                    ->implode(', ');

                Movement::create([
                    'product_id' => $movement->product_id,
                    'warehouse_id' => $movement->warehouse_id,
                    'type' => 'out',
                    'status' => 'completed',
                    'reason' => 'Liquidación de salida',
                    'quantity' => $movement->quantity,
                    'note' => "Resultados: {$summary}",
                    'user_id' => (int) auth()->id(),
                    'technician_id' => $movement->technician_id,
                    'movement_kind' => 'liquidation',
                    'movement_group_id' => $movement->movement_group_id,
                    'parent_movement_id' => $movement->id,
                    'reference_code' => 'LIQ-' . str_replace('SAL-', '', (string) $movement->reference_code),
                ]);
            }

            $finalStatuses = $movement->items()->pluck('final_status');
            if ($finalStatuses->isNotEmpty()) {
                $currentStatus = $finalStatuses->contains('in_use')
                    ? 'in_use'
                    : ($finalStatuses->contains('damaged')
                        ? 'damaged'
                        : ($finalStatuses->contains('under_repair')
                            ? 'under_repair'
                            : ($finalStatuses->contains('lost')
                                ? 'lost'
                                : ($finalStatuses->contains('disposed')
                                    ? 'disposed'
                                    : ($finalStatuses->every(fn ($status) => $status === 'returned') ? 'available' : 'other')))));
                Product::whereKey($movement->product_id)->update(['current_status' => $currentStatus]);
            }
        });

        return redirect()
            ->route('movements.show', $movement)
            ->with('ok', 'Liquidación guardada correctamente.');
    }

    public function storeTransfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'source_warehouse_id' => ['required', 'exists:warehouses,id', 'different:destination_warehouse_id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data): void {
            $product = Product::where('type', 'service')->findOrFail($data['product_id']);
            $stock = ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $data['source_warehouse_id']],
                ['current_stock' => 0]
            );

            if ($stock->current_stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuficiente en el almacén origen.',
                ]);
            }

            $stock->decrement('current_stock', $data['quantity']);
            $stock->refresh();
            $product->update([
                'quantity' => ProductStock::where('product_id', $product->id)->sum('current_stock'),
            ]);

            $transfer = StockTransfer::create([
                'reference_code' => 'TMP-' . Str::uuid(),
                'product_id' => $product->id,
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'quantity' => $data['quantity'],
                'status' => 'in_transit',
                'created_by' => (int) auth()->id(),
                'note' => $data['note'] ?? null,
            ]);
            $transfer->update(['reference_code' => 'TRF-' . str_pad((string) $transfer->id, 6, '0', STR_PAD_LEFT)]);

            Movement::create([
                'product_id' => $product->id,
                'warehouse_id' => $data['source_warehouse_id'],
                'type' => 'out',
                'status' => 'in_transit',
                'reason' => 'Transferencia entre almacenes',
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
                'user_id' => (int) auth()->id(),
                'movement_kind' => 'transfer_out',
                'movement_group_id' => (string) Str::uuid(),
                'reference_code' => $transfer->reference_code,
                'transfer_id' => $transfer->id,
            ]);
        });

        return redirect()->route('movements.index')->with('ok', 'Transferencia creada y enviada.');
    }

    public function receiveTransfer(StockTransfer $transfer): RedirectResponse
    {
        DB::transaction(function () use ($transfer): void {
            $transfer->refresh();
            abort_unless($transfer->status === 'in_transit', 422, 'La transferencia ya fue procesada.');

            $stock = ProductStock::firstOrCreate(
                ['product_id' => $transfer->product_id, 'warehouse_id' => $transfer->destination_warehouse_id],
                ['current_stock' => 0]
            );
            $stock->increment('current_stock', $transfer->quantity);

            $product = $transfer->product;
            $product->update([
                'quantity' => ProductStock::where('product_id', $product->id)->sum('current_stock'),
            ]);

            $outMovement = $transfer->movements()->where('movement_kind', 'transfer_out')->firstOrFail();
            Movement::create([
                'product_id' => $transfer->product_id,
                'warehouse_id' => $transfer->destination_warehouse_id,
                'type' => 'in',
                'status' => 'received',
                'reason' => 'Transferencia recibida',
                'quantity' => $transfer->quantity,
                'note' => $transfer->note,
                'user_id' => (int) auth()->id(),
                'movement_kind' => 'transfer_in',
                'movement_group_id' => $outMovement->movement_group_id,
                'parent_movement_id' => $outMovement->id,
                'reference_code' => $transfer->reference_code,
                'transfer_id' => $transfer->id,
            ]);

            $transfer->update([
                'status' => 'received',
                'received_by' => (int) auth()->id(),
                'received_at' => now(),
            ]);
        });

        return redirect()->route('movements.index')->with('ok', 'Transferencia recibida y entrada registrada.');
    }

    public function cancelTransfer(StockTransfer $transfer): RedirectResponse
    {
        DB::transaction(function () use ($transfer): void {
            $transfer->refresh();
            abort_unless($transfer->status === 'in_transit', 422, 'La transferencia ya fue procesada.');

            $stock = ProductStock::firstOrCreate(
                ['product_id' => $transfer->product_id, 'warehouse_id' => $transfer->source_warehouse_id],
                ['current_stock' => 0]
            );
            $stock->increment('current_stock', $transfer->quantity);
            $transfer->product->update([
                'quantity' => ProductStock::where('product_id', $transfer->product_id)->sum('current_stock'),
            ]);
            $transfer->update(['status' => 'cancelled']);
            $transfer->movements()->where('movement_kind', 'transfer_out')->update(['status' => 'cancelled']);
        });

        return redirect()->route('movements.index')->with('ok', 'Transferencia cancelada y stock restaurado.');
    }
}
