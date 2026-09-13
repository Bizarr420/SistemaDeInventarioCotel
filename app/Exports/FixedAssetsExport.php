<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FixedAssetsExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $assets)
    {
    }

    public function collection(): Collection
    {
        return $this->assets->map(fn ($asset) => [
            $asset->internal_code,
            $asset->name_item,
            $asset->category?->name,
            $asset->supplier?->name,
            $asset->location_branch,
            $asset->location_floor,
            $asset->location_office,
            $asset->assigned_to,
            $asset->assigned_department,
            $asset->quantity,
            $asset->unit_cost,
            $asset->asset_status,
            $asset->obsolete_disposition_status,
            optional($asset->latestVerification?->verified_at)->format('Y-m-d'),
        ]);
    }

    public function headings(): array
    {
        return [
            'Código interno', 'Activo', 'Categoría', 'Proveedor', 'Sucursal',
            'Piso', 'Oficina', 'Responsable', 'Departamento', 'Cantidad',
            'Valor unitario', 'Estado', 'Subestado', 'Última verificación',
        ];
    }
}
