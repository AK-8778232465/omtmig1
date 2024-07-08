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
                'process' => $row['process'] ?? null,
                'municipality' => $row['municipality'] ?? null,
                'lob' => $row['lob'] ?? null,
                'json' => [
                    'TAX' => [
                        'TAX_SITE' => $row['tax_site'] ?? null,
                        'TAX_USERNAME' => $row['tax_site_username'] ?? null,
                        'TAX_PASSWORD' => $row['tax_site_password'] ?? null,
                    ],
                    'COURT' => [
                        'COURT_SITE' => $row['court_site'] ?? null,
                        'COURT_USERNAME' => $row['court_username'] ?? null,
                        'COURT_PASSWORD' => $row['court_password'] ?? null,
                    ],
                    'PRIMARY' => [
                        'PRIMARY_SOURCE' => $row['primary_source'] ?? null,
                    ],
                    'RECORDER' => [
                        'RECORDER_SITE' => $row['recorder_site'] ?? null,
                        'RECORDER_USERNAME' => $row['recorder_username'] ?? null,
                        'RECORDER_PASSWORD' => $row['recorder_password'] ?? null,
                    ],
                    'PROBATE_COURT' => [
                        'PROBATE_LINK' => $row['probate_court_link'] ?? null,
                        'PROBATE_USERNAME' => $row['probate_court_username'] ?? null,
                        'PROBATE_PASSWORD' => $row['probate_court_password'] ?? null,
                    ],
                    'ASSESSOR' => [
                        'ASSESSOR_SITE' => $row['assessor_site'] ?? null,
                        'ASSESSOR_USERNAME' => $row['assessor_username'] ?? null,
                        'ASSESSOR_PASSWORD'  => $row['assessor_password'] ?? null,
                    ],
                    'MUNICIPALITY' =>  [
                        'MUNICIPALITY' => $row['municipality'] ?? null,
                        'STATE_COUNTY' => $row['statecounty'] ?? null,
                        'STATE_COUNTY_TOWNSHIP' => $row['statecountytownship'] ?? null,
                    ]
                ]
            ];

            $this->data->push($entry);
        }
    }
}

