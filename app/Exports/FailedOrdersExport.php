<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Carbon;

class FailedOrdersExport implements FromCollection, WithHeadings
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
                Carbon::parse($row->order_date)->format('m/d/Y H:i:s'),
                $row->order_id,
                $row->assignee_user,
                $row->assignee_qa,
                $row->process,
                $row->lob,
                $row->state,
                $row->county,
                $row->city,
                $row->status,
                $row->tier,
                $row->typist_qc_id,
                $row->typist_id,
                $row->comments,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Order Received Date and Time',
            'OrderID',
            'Emp ID-Order Assigned',
            'Assignee_QA',
            'Product Name',
            'Lob',
            'State',
            'County',
            'Municipality',
            'Status',
            'tier',
            'Typist',
            'Typist QC',
            'Comments',
        ];
    }
}
