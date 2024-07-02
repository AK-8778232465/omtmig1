<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class CountyImport implements ToCollection, WithHeadingRow
{
    public $data;

    public function __construct()
    {
        $this->data = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $entry = [
                'state' => $row['state'] ?? null,
                'county' => $row['county'] ?? null,
                'process_id' => $row['process_id'] ?? null,
                'lob' => $row['lob'] ?? null,
                'json' => [
                    'TAX' => [
                        'TAX' => $row['TAX'] ?? null,
                        'TAX_SITE' => $row['TAX SITE'] ?? null,
                        'TAX_PASSWORD' => $row['TAX SITE PASSWORD'] ?? null,
                        'TAX_USERNAME' => $row['TAX SITE USERNAME'] ?? null,
                    ],
                    'COURT' => [
                        'COURT' => $row['sample5'] ?? null,
                        'COURT_SITE' => $row['COURT SITE'] ?? null,
                        'COURT_PASSWORD' => $row['COURT PASSWORD'] ?? null,
                        'COURT_USERNAME' => $row['COURT USERNAME'] ?? null,
                    ],
                    'PRIMARY' => [
                        'PRIMARY_SOURCE' => $row['PRIMARY SOURCE'] ?? null,
                        'PRIMARY_SOURCE_STD' => $row['sample15'] ?? null,
                        'PRIMARY_IMAGE_SOURCE' => $row['sample21'] ?? null,
                        'ONLINE_GEO_START_DATE' => $row['sample22'] ?? null,
                        'PRIMARY_IMAGE_START_DATE' => $row['sample23'] ?? null,
                        'PRIMARY_SEARCH_START_DATE' => $row['sample24'] ?? null,
                    ],
                    'ASSESSOR' => [
                        'ASSESSOR' => $row['sample25'] ?? null,
                        'ASSESSOR_SITE' => $row['ASSESSOR SITE'] ?? null,
                        'ASSESSOR_PASSWORD' => $row['ASSESSOR PASSWORD'] ?? null,
                        'ASSESSOR_USERNAME' => $row['ASSESSOR USERNAME'] ?? null,
                    ],
                ]
            ];

            $this->data->push($entry);
        }
    }
}

