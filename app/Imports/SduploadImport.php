<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use App\Models\County;
use App\Models\CountyInstructions;
use App\Models\State;
use App\Models\City;
use App\Models\Client;
use App\Models\stlprocess;
use App\Models\Lob;
use App\Models\CountyInstructionTemp;
use App\Models\CountyInstructionAudit;
use App\Models\User;
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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTime;


HeadingRowFormatter::default('none');

class SduploadImport implements ToModel, ShouldQueue, WithEvents, WithHeadingRow, WithBatchInserts, WithCalculatedFormulas, WithChunkReading, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $userid;
    protected $auditId;
    protected $client_id;
    protected $lob_id;
    protected $process_id;
    protected $rows = 0;
    protected $success_rows = 0;
    protected $unsuccess_rows = 0;

    public function __construct($userid, $auditId, $client_id, $lob_id, $process_id)
    {
       
        $this->userid = $userid;
        $this->auditId = $auditId;
        $this->client_id = $client_id;
        $this->lob_id = $lob_id;
        $this->process_id = $process_id;
    }

    public function model(array $row)
    {
        ++$this->rows;

        $getLob = Lob::where('id', $this->lob_id)->first();
        $getClient = Client::where('id', $this->client_id)->first();
        $getProcess = stlprocess::where('id', $this->process_id)->first();
        $getUser = User::where('id', $this->userid)->first();
        $data = [
            'client' => $getClient->client_name,
            'lob' => $getLob->name,
            'process' => $getProcess->name,
            'state' => isset($row['STATE']) ? $row['STATE'] : null,
            'county' => isset($row['COUNTY']) ? $row['COUNTY'] : null,
            'city' => isset($row['Town/City/Municipality']) ? strtoupper($row['Town/City/Municipality']) : (isset($row['MUNICIPALITY']) ? strtoupper($row['MUNICIPALITY']) : null),
            'created_at' => now(),
            'created_by' => $getUser->username,
            'audit_id' => $this->auditId,
        ];

        $state = null;
        $stateCode = strtoupper($row['STATE']);
       
        if ($stateCode) {
            $state = State::where('short_code', $stateCode)->first();
        } else {
            $data['comments'] = 'State is Invaild';
            CountyInstructionTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $county = null;
        $countyCode = strtoupper($row['COUNTY']);
        if ($state) {
            $county = County::where('county_name', $countyCode)
                ->where('stateId', $state->id)
                ->first();
        } else {
            $data['comments'] = 'County is Invaild';
            CountyInstructionTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $city = null;
        $cityCode = isset($row['Town/City/Municipality']) ? strtoupper($row['Town/City/Municipality']) : (isset($row['MUNICIPALITY']) ? strtoupper($row['MUNICIPALITY']) : null);

        if ($county) {
            $city = City::where('city', $cityCode)
                ->where('county_id', $county->id)
                ->first();
        } else {
            $data['comments'] = 'Municipality is Invaild';
            CountyInstructionTemp::insert($data);
            ++$this->unsuccess_rows;
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
                'ASSESSOR_PASSWORD' => $row['ASSESSOR PASSWORD'] ?? null,
            ],
            'MUNICIPALITY' => [
                'MUNICIPALITY' => $row['MUNICIPALITY'] ?? $row['Town/City/Municipality'] ?? null,
                'STATE_COUNTY' => $row['STATE'] ?? null,
                'STATE_COUNTY_TOWNSHIP' => $row['COUNTY'] ?? null,
            ]
        ];

        try {
            if (!$city) {
                $existingEntry = CountyInstructions::where('state_id', $state->id)
                    ->where('county_id', $county->id)
                    ->where('client_id', $this->client_id)
                    ->where('process_id', $this->process_id)
                    ->where('lob_id', $this->lob_id)
                    ->first();
            } else {
                $existingEntry = CountyInstructions::where('state_id', $state->id)
                    ->where('county_id', $county->id)
                    ->where('city_id', $city->id)
                    ->where('client_id', $this->client_id)
                    ->where('process_id', $this->process_id)
                    ->where('lob_id', $this->lob_id)
                    ->first();
            }

            if ($existingEntry) {
                $existingJson = json_decode($existingEntry->json, true);

                foreach ($jsonData as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            if ($subValue !== null && $subValue !== '' && $subValue !== '--') {
                                $existingJson[$key][$subKey] = $subValue;
                            }
                        }
                    } else {
                        if ($value !== null && $value !== '' && $value !== '--') {
                            $existingJson[$key] = $value;
                        }
                    }
                }

                $updatedJsonString = json_encode($existingJson);

                $existingEntry->update(['json' => $updatedJsonString, 'last_updated_by' => $this->userid, 'updated_at' => now()]);
                ++$this->success_rows;
            } else {
                if (!$city) {
                    DB::transaction(function () use ($state, $county, $jsonData) {
                        CountyInstructions::insert([
                            'client_id' => $this->client_id,
                            'lob_id' => $this->lob_id,
                            'process_id' => $this->process_id,
                            'state_id' => $state->id,
                            'county_id' => $county->id,
                            'json' => json_encode($jsonData),
                            'created_by' => $this->userid,
                            'created_at' => now(),
                        ]);
                    });
                } else {
                    DB::transaction(function () use ($state, $county, $city, $jsonData) {
                        CountyInstructions::insert([
                            'client_id' => $this->client_id,
                            'lob_id' => $this->lob_id,
                            'process_id' => $this->process_id,
                            'state_id' => $state->id,
                            'county_id' => $county->id,
                            'city_id' => $city->id,
                            'json' => json_encode($jsonData),
                            'created_by' => $this->userid,
                            'created_at' => now(),
                        ]);
                    });
                }
                ++$this->success_rows;
            }
        } catch (\Exception $e) {
            ++$this->unsuccess_rows;
            return null; 
        }
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
        $import = $this;

        return [
            AfterBatch::class => function (AfterBatch $event) use ($import) {
                $import->afterBatch($event);
            },
        ];
    }

    public function afterBatch(AfterBatch $event)
    {
        if ($this->auditId) {
            try {
                DB::beginTransaction();
                $oldData = CountyInstructionAudit::lockForUpdate()->find($this->auditId);
                CountyInstructionAudit::where('id', $oldData->id)->update([
                    'successfull_rows' => $oldData->successfull_rows + $this->success_rows,
                    'unsuccessfull_rows' => $oldData->unsuccessfull_rows + $this->unsuccess_rows,
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                Log::error("Error updating OrderCreationAudit: " . $e->getMessage());
            }
        }
    
        return [];
    }
    

    public function skipRow()
    {
        // Custom logic for skipping a row, if needed
    }
}
