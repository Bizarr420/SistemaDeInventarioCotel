<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditEventService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'module' => 'nullable|string|max:80',
            'action' => 'nullable|string|max:80',
            'ip_address' => 'nullable|ip',
            'result' => 'nullable|in:success,rejected,failed',
            'entity_type' => 'nullable|string|max:160',
            'entity_id' => 'nullable|string|max:80',
            'search' => 'nullable|string|max:160',
        ]);
        $logs = $this->filteredQuery($filters)
            ->with('user')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $modules = AuditLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit_logs.index', compact('logs', 'users', 'modules', 'actions', 'filters'));
    }

    public function show(AuditLog $auditLog): View
    {
        return view('audit_logs.show', ['log' => $auditLog->load('user')]);
    }

    public function entityHistory(string $entityType, string $entityId): View
    {
        $allowedTypes = [
            'FixedAsset', 'FixedAssetMovement', 'FixedAssetDepreciationParameter',
            'FixedAssetDepreciationEntry', 'AccountingAdjustment', 'Product',
            'Movement', 'MovementItem', 'User', 'Role', 'Dictamen',
        ];
        abort_unless(in_array($entityType, $allowedTypes, true), 404);
        $class = 'App\\Models\\'.$entityType;
        $logs = AuditLog::query()
            ->where(function (Builder $query) use ($class, $entityType, $entityId): void {
                $query->where(function (Builder $legacy) use ($class, $entityId): void {
                    $legacy->where('auditable_type', $class)->where('auditable_id', is_numeric($entityId) ? (int) $entityId : 0);
                })->orWhere(function (Builder $modern) use ($entityType, $entityId): void {
                    $modern->where('entity_type', $class)->where('entity_id', $entityId);
                });
            })
            ->with('user')
            ->orderByDesc('id')
            ->paginate(30);

        return view('audit_logs.history', compact('logs', 'entityType', 'entityId'));
    }

    public function export(Request $request, AuditEventService $audit): StreamedResponse
    {
        $filters = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'module' => 'nullable|string|max:80',
            'action' => 'nullable|string|max:80',
            'ip_address' => 'nullable|ip',
            'result' => 'nullable|in:success,rejected,failed',
            'entity_type' => 'nullable|string|max:160',
            'entity_id' => 'nullable|string|max:80',
            'search' => 'nullable|string|max:160',
        ]);
        $audit->record('audit', 'export', null, 'Exportación CSV de eventos de auditoría.',
            newValues: ['filters' => $filters], request: $request);
        $query = $this->filteredQuery($filters)->with('user')->orderBy('id');

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Fecha local', 'UTC', 'Usuario', 'Módulo', 'Acción', 'Entidad', 'Registro', 'IP', 'Navegador', 'Resultado', 'Descripción']);
            $query->chunk(500, function ($logs) use ($stream): void {
                $escapeSpreadsheetFormula = static function ($value): string {
                    $value = (string) $value;

                    return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
                };
                foreach ($logs as $log) {
                    $created = $log->created_at;
                    fputcsv($stream, array_map($escapeSpreadsheetFormula, [
                        $created?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        $created?->utc()->format('Y-m-d\TH:i:s\Z'),
                        $log->user?->name ?? 'Sistema',
                        $log->module,
                        $log->action ?: $log->event,
                        $log->entity_type ?: class_basename($log->auditable_type),
                        $log->entity_id ?: $log->auditable_id,
                        $log->ip_address,
                        $log->browser,
                        $log->result,
                        $log->description,
                    ]));
                }
            });
            fclose($stream);
        }, 'sicat-auditoria-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredQuery(array $filters): Builder
    {
        return AuditLog::query()
            ->when($filters['user_id'] ?? null, fn (Builder $query, $value) => $query->where('user_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $value) => $query->whereDate('created_at', '<=', $value))
            ->when($filters['module'] ?? null, fn (Builder $query, $value) => $query->where('module', $value))
            ->when($filters['action'] ?? null, fn (Builder $query, $value) => $query->where(function (Builder $builder) use ($value): void {
                $builder->where('action', $value)->orWhere('event', $value);
            }))
            ->when($filters['ip_address'] ?? null, fn (Builder $query, $value) => $query->where('ip_address', $value))
            ->when($filters['result'] ?? null, fn (Builder $query, $value) => $query->where('result', $value))
            ->when($filters['entity_type'] ?? null, fn (Builder $query, $value) => $query->where(function (Builder $builder) use ($value): void {
                $builder->where('entity_type', $value)->orWhere('auditable_type', 'like', '%\\'.$value);
            }))
            ->when($filters['entity_id'] ?? null, fn (Builder $query, $value) => $query->where(function (Builder $builder) use ($value): void {
                $builder->where('entity_id', $value)->orWhere('auditable_id', $value);
            }))
            ->when($filters['search'] ?? null, fn (Builder $query, $value) => $query->where(function (Builder $builder) use ($value): void {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $value).'%';
                $builder->where('description', 'like', $like)
                    ->orWhere('module', 'like', $like)
                    ->orWhere('action', 'like', $like)
                    ->orWhere('entity_type', 'like', $like)
                    ->orWhere('entity_id', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $like)->orWhere('email', 'like', $like));
            }));
    }
}
