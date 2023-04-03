<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BuildHoldDropExport implements FromCollection, WithHeadings
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
            if (is_object($value)) {
                $data[] = [
                    'md' => ($value->md) ? $value->md : '0',
                    'inclination' => ($value->status == 'Target') ? '0' : $value->inclination,
                    'tvd' => ($value->tvd) ? $value->tvd : '0',
                    'total_departure' => ($value->total_departure) ? $value->total_departure : '0',
                    'status' => $value->status,
                ];
            } else {
                $data[] = [
                    'md' => ($value['md']) ? $value['md'] : '0',
                    'inclination' => ($value['status'] == 'Target') ? '0' : $value['inclination'],
                    'tvd' => ($value['tvd']) ? $value['tvd'] : '0',
                    'total_departure' => ($value['total_departure']) ? $value['total_departure'] : '0',
                    'status' => $value['status'],
                ];
            }
        }
        
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Measure Depth',
            'Inclination',
            'TVD',
            'Total Departure',
            'Status',
        ];
    }
}
