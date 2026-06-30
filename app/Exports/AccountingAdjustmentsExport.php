<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AccountingAdjustmentsExport implements FromCollection, WithHeadings
{
    public function __construct(protected $adjustments)
    {
    }

    public function collection()
    {
        return collect($this->adjustments);
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Activo',
            'Código Interno',
            'Dictamen ID',
            'Técnico',
            'Contable',
            'Reconocido',
            'Estado',
            'Tipo de Ajuste',
            'Descripción',
        ];
    }
}