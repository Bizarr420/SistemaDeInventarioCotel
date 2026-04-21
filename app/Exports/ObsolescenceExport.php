<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ObsolescenceExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return collect($this->products);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Código Interno',
            '¿Obsoleto?',
            'Vida Útil (%)',
            'Deterioro (%)',
            'Desviación Patrimonial',
            'Fin de Soporte',
            'Estado Compatibilidad',
            'Capacidad Operacional',
        ];
    }
}