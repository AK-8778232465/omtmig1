<?php

namespace App\Imports;


use Illuminate\Support\Facades\Log;
use App\Models\County;
use App\Models\OrderCreationAudit;
use App\Models\OrderCreation;
use App\Models\OrderTemp;
use App\Models\Process;
use App\Models\State;
use App\Models\City;
use App\Models\Status;
use App\Models\Countyinstructions;
use App\Models\Lob;
use App\Models\Tier;
use App\Models\User;
use Carbon\Carbon;
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
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use DateTime;

HeadingRowFormatter::default('none');

class SduploadImport implements ToModel, ShouldQueue, WithEvents, WithHeadingRow, WithBatchInserts, WithCalculatedFormulas, WithChunkReading, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $userid;
    protected $client_id;
    protected $lob_id;
    protected $process_id;
    protected $rows = 0;

    public function __construct($userid,$client_id, $lob_id, $process_id)
    {
        $this->userid = $userid;
        $this->client_id = $client_id;
        $this->lob_id = $lob_id;
        $this->process_id = $process_id;
    }


    public function model(array $row)
    {
        $state = State::where('short_code', $row['STATE'])->first();
        $county = null;

        if ($state) {
            $county = County::where('county_name', $row['COUNTY'])
                            ->where('stateId', $state->id)
                            ->first();
        }

        $city = null;

        if ($county) {
            $city = City::where('city', $row['Town/City/Municipality'])
                            ->where('county_id', $county->id)
                            ->first();
        }
    
        // Prepare the JSON data
        $jsonData = [
            'STATE' => $row['STATE'] ?? null,
            'COUNTY' => $row['COUNTY'] ?? null,
            'TOWN/CITY/MUNICIPALITY' => $row['Town/City/Municipality'] ?? null,
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
                'MUNICIPALITY' => $row['MUNICIPALITY'] ?? null,
                'STATE_COUNTY' => $row['STATE'] ?? null,
                'STATE_COUNTY_TOWNSHIP' => $row['COUNTY'] ?? null,
            ]
        ];
    
        // Convert to JSON string
        $jsonString = json_encode($jsonData);
    
        // Log the entry for debugging or auditing purposes
        Log::info('Processing entry: ' . $jsonString);
    
        
    try {
        // Check if there's an existing entry
        $existingEntry = Countyinstructions::where('state_id', $state ? $state->id : null)
            ->where('county_id', $county ? $county->id : null)
            ->where('client_id', $this->client_id)
            ->where('city_id', $city ? $city->id : null)
            ->where('process_id', $this->process_id)
            ->where('lob_id', $this->lob_id)
            ->first();

        if ($existingEntry) {
            // Update existing entry
            $existingEntry->update(['json' => $jsonString]);
        } else {
            // Insert new entry within a transaction for data integrity
            DB::transaction(function () use ($state, $county, $city, $jsonString) {
                Countyinstructions::create([
                    'client_id' => $this->client_id,
                    'lob_id' => $this->lob_id,
                    'process_id' => $this->process_id,
                    'state_id' => $state ? $state->id : null,
                    'county_id' => $county ? $county->id : null,
                    'city_id' => $city ? $city->id : null,
                    'json' => $jsonString,
                ]);
            });
        }
    } catch (\Exception $e) {
        // Log the error and handle as needed
        Log::error('Error importing row: ' . $jsonString . ' Error: ' . $e->getMessage());
        $this->skipRow(); // Skip the row on failure
    }
    $this->rows++;
}
    public function batchSize(): int
    {
        return 1000; // Adjust batch size as needed
    }

    public function chunkSize(): int
    {
        return 1000; // Adjust chunk size as needed
    }

    public function startRow(): int
    {
        return 2; // Skip header row
    }

    public function registerEvents(): array
    {
        return [
            AfterBatch::class => function (AfterBatch $event) {
                Log::info("Processed {$this->rows} rows in this batch."); // Log processed rows count
                $this->rows = 0; // Reset processed rows count for next batch
            },
        ];
    }

    public function skipRow() // Implement the skipRow method from SkipsOnFailure
    {
        // Custom logic for skipping a row, if needed
    }
}
