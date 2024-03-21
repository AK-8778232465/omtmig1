<?php

namespace App\Imports;

use App\Models\ServiceUserMapping;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use App\Models\Process;
use App\Models\User;

HeadingRowFormatter::default('none');

class AssignImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, ShouldQueue, WithCalculatedFormulas
{
    public function model(array $row)
    {
        $serviceCode = trim($row['Project Code']);
        $employeeID = trim($row['Employee ID']);

        if (empty($serviceCode) || empty($employeeID)) {
            return null;
        }

        $service = Process::where('project_code', $serviceCode)->first();
        $user = User::where('emp_id', $employeeID)->first();

        if (!$service || !$user) {
            return null;
        }

        $existingMapping = ServiceUserMapping::where('service_id', $service->id)
                                             ->where('user_id', $user->id)
                                             ->first();

        if ($existingMapping) {
            $existingMapping->update([
                'is_active' => (strtoupper($row['Status']) == 'ACTIVE') ? 1 : 0
            ]);
        } else {
            $data = [
                'service_id' => $service->id,
                'user_id' => $user->id,
                'is_active' => (strtoupper($row['Status']) == 'ACTIVE') ? 1 : 0
            ];

            ServiceUserMapping::create($data);
        }
    }


    public function batchSize(): int
    {
        return 1500;
    }

    public function chunkSize(): int
    {
        return 1500;
    }
}
