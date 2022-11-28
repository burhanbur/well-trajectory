<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BuildHoldExport implements FromCollection, WithHeadings
{
    use Exportable;
    
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            $data[] = [
                'md' => $value->md,
                'inclination' => $value->inclination,
                'tvd' => $value->tvd,
                'total_departure' => $value->total_departure,
                'status' => $value->status,
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'MD',
            'Inclination',
            'TVD',
            'Total Departure',
            'Status',
        ];
    }
}
