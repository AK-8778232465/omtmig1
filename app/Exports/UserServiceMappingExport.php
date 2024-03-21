<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserServiceMappingExport implements FromCollection, WithHeadings
{
    protected $mappedList;

    public function __construct(Collection $mappedList)
    {
        $this->mappedList = $mappedList;
    }

    public function collection()
    {
        return $this->mappedList->map(function ($row) {
            return [
                $row->users->emp_id,
                $row->users->username,
                $row->projects->project_code,
                ($row->is_active == 1) ? 'Active' : 'Inactive',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Username',
            'Project Code',
            'Status'
        ];
    }
}
