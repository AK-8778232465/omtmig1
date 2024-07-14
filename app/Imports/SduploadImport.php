<?php

namespace App\Imports;


use Illuminate\Support\Facades\Log;
use App\Models\County;
use App\Models\CountyInstructions;
use App\Models\State;
use App\Models\City;
use DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterBatch;
use Illuminate\Contracts\Queue\ShouldQueue;

HeadingRowFormatter::default('none');

class SduploadImport implements ToModel, ShouldQueue, WithEvents, WithHeadingRow, WithBatchInserts, WithCalculatedFormulas, WithChunkReading, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $userid;
    protected $client_id;
    protected $lob_id;
    protected $process_id;
    protected $rows = 0;

    public function __construct($userid, $client_id, $lob_id, $process_id)
    {
        $this->userid = $userid;
        $this->client_id = $client_id;
        $this->lob_id = $lob_id;
        $this->process_id = $process_id;
    }


    public function model(array $row)
    {
        $stateCode = strtoupper($row['STATE']);
        $state = State::where('short_code', $stateCode)->first();
        $county = null;

        $countyCode = strtoupper($row['COUNTY']);
        if ($state) {
            $county = County::where('county_name', $countyCode)
                            ->where('stateId', $state->id)
                            ->first();
        } else {
            Log::error($row);
            return null;
        }

        $city = null;
        $cityCode = isset($row['Town/City/Municipality']) ? strtoupper($row['Town/City/Municipality']) : (isset($row['MUNICIPALITY']) ? strtoupper($row['MUNICIPALITY']) : null);

        if ($county) {
            $city = City::where('city', $cityCode)
                            ->where('county_id', $county->id)
                            ->first();
        } else {
            Log::error($row);
            return null;
        }
    
        $jsonData = [
            'TAX' => [
                'TAX_SITE' => $row['TAX SITE'] ?? null,
                'TAX_USERNAME' => $row['TAX SITE USERNAME'] ?? null,
                'TAX_PASSWORD' => $row['TAX SITE PASSWORD'] ?? null,
            ],
            'COURT' => [
                'COURT_SITE' => $row['COURT SITE'] ?? null,
                'COURT_USERNAME' => $row['COURT USERNAME'] ?? null,
                'COURT_PASSWORD' => $row['COURT PASSWORD'] ?? null,
            ],
            'PRIMARY' => [
                'PRIMARY_SOURCE' => $row['PRIMARY SOURCE'] ?? null,
            ],
            'RECORDER' => [
                'RECORDER_SITE' => $row['RECORDER SITE'] ?? null,
                'RECORDER_USERNAME' => $row['RECORDER USERNAME'] ?? null,
                'RECORDER_PASSWORD' => $row['RECORDER PASSWORD'] ?? null,
            ],
            'PROBATE_COURT' => [
                'PROBATE_LINK' => $row['PROBATE COURT LINK'] ?? null,
                'PROBATE_USERNAME' => $row['PROBATE COURT USERNAME'] ?? null,
                'PROBATE_PASSWORD' => $row['PROBATE COURT PASSWORD'] ?? null,
            ],
            'ASSESSOR' => [
                'ASSESSOR_SITE' => $row['ASSESSOR SITE'] ?? null,
                'ASSESSOR_USERNAME' => $row['ASSESSOR USERNAME'] ?? null,
                'ASSESSOR_PASSWORD'  => $row['ASSESSOR PASSWORD'] ?? null,
            ],
            'MUNICIPALITY' =>  [
                'MUNICIPALITY' => $row['MUNICIPALITY'] ?? $row['Town/City/Municipality'] ?? null,
                'STATE_COUNTY' => $row['STATE'] ?? null,
                'STATE_COUNTY_TOWNSHIP' => $row['COUNTY'] ?? null,
            ]
        ];
    
        try {
            if (!$city) {
                $existingEntry = CountyInstructions::where('state_id', $state->id)
                ->where('county_id', $county->id)
                ->first();
            } else {
                $existingEntry = CountyInstructions::where('state_id', $state->id)
                ->where('county_id', $county->id)
                ->where('city_id', $city->id)
            ->first();
            }
           
            if ($existingEntry) {
                $existingJson = json_decode($existingEntry->json, true);

                foreach ($jsonData as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            if ($subValue !== null) {
                                $existingJson[$key][$subKey] = $subValue;
                            }
                        }
                    } else {
                        if ($value !== null) {
                            $existingJson[$key] = $value;
                        }
                    }
                }

                $updatedJsonString = json_encode($existingJson);

                $existingEntry->update(['json' => $updatedJsonString]);
        } else {
                if (!$city) {
                    DB::transaction(function () use ($state, $county, $jsonData) {
                        CountyInstructions::create([
                            'client_id' => $this->client_id,
                            'lob_id' => $this->lob_id,
                            'process_id' => $this->process_id,
                            'state_id' => $state->id,
                            'county_id' => $county->id,
                            'json' => json_encode($jsonData),
                        ]);
                    });
                } else {
                    DB::transaction(function () use ($state, $county, $city, $jsonData) {
                        CountyInstructions::create([
                    'client_id' => $this->client_id,
                    'lob_id' => $this->lob_id,
                    'process_id' => $this->process_id,
                            'state_id' => $state->id,
                            'county_id' => $county->id,
                            'city_id' => $city->id,
                            'json' => json_encode($jsonData),
                ]);
            });
        }
            }
    } catch (\Exception $e) {
            Log::error('Error importing row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            $this->skipRow(); 
    }
    $this->rows++;
    }
    public function batchSize(): int
    {
        return 1000; 
    }

    public function chunkSize(): int
    {
        return 1000; 
    }

    public function startRow(): int
    {
        return 2; 
    }

    public function registerEvents(): array
    {
        return [
            AfterBatch::class => function (AfterBatch $event) {
                Log::info("Processed {$this->rows} rows in this batch."); 
                $this->rows = 0; 
            },
        ];
    }

    public function skipRow() 
    {
        // Custom logic for skipping a row, if needed
    }
}
