<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Carbon;

class FailedCIOrdersExport implements FromCollection, WithHeadings
{
    protected $failedOrders;

    public function __construct(Collection $failedOrders)
    {
        $this->failedOrders = $failedOrders;
    }

    public function collection()
    {
        return $this->failedOrders->map(function ($row) {
            return [
                $row->state,
                $row->county,
                $row->client,
                $row->city,
                $row->process,
                $row->lob,
                $row->comments,
                $row->created_by,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'STATE',
            'COUNTY',
            'CLIENT',
            'MUNICIPALITY',
            'PROCESS',
            'LOB',
            'COMMENTS',
            'CREATED_BY',
        ];
    }
}
