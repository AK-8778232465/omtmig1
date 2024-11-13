<?php

namespace App\Imports;

use App\Models\County;
use App\Models\OrderCreationAudit;
use App\Models\OrderCreation;
use App\Models\OrderTemp;
use App\Models\Process;
use App\Models\State;
use App\Models\City;
use App\Models\Status;

use App\Models\Lob;
use App\Models\Tier;
use App\Models\User;
use App\Models\stlprocess;
use App\Models\Client;

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

class OrdersCreationImport implements ToModel, ShouldQueue, WithEvents, WithHeadingRow, WithBatchInserts, WithCalculatedFormulas, WithChunkReading, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $userid;
    protected $auditId;

    protected $rows = 0;
    protected $success_rows = 0;
    protected $unsuccess_rows = 0;

    public function __construct($userid, $auditId)
    {
        $this->auditId = $auditId;
        $this->userid = $userid;
    }

    public function model(array $row)
    {
        ++$this->rows;
        $orderDateValue = $row['Order Received Data and Time'];

        $order_date = NULL;
        if (is_numeric($orderDateValue)) {
            // Assuming the timestamp is in seconds, if it's in milliseconds, you need to adjust accordingly
            $order_date = date('Y-m-d H:i:s', strtotime('1899-12-30') +round($orderDateValue * 86400));
            $order_date =  Carbon::parse($order_date);
            $order_date->subSeconds(8 * 60 + 50);
            
        } else {
            $dateFormats = ['m/d/Y H:i:s', 'm-d-Y H:i:s', 'm/d/Y', 'm-d-Y'];
            $parsedDateTime = null;
            foreach ($dateFormats as $format) {
                $dateTime = DateTime::createFromFormat($format, $orderDateValue);
                if ($dateTime !== false) {
                    $parsedDateTime = $dateTime->format('Y-m-d H:i:s');
                    break;
                }
            }
            $order_date = $parsedDateTime ?? NULL;
        }


        $data = [
            'order_date' => $order_date,
            'order_id' => isset($row['OrderID']) ? $row['OrderID'] : null,
            'client_id' => isset($row['Client']) ? $row['Client'] : null,
            'assignee_user' => isset($row['Emp ID-Order Assigned']) ? $row['Emp ID-Order Assigned'] : null,
            'assignee_qa' => isset($row['Assignee_QA']) ? $row['Assignee_QA'] : null,
            'process' => isset($row['Product Name']) ? $row['Product Name'] : null,
            'lob' => isset($row['Lob']) ? $row['Lob'] : null,
            'process_type_id' => isset($row['Process']) ? $row['Process'] : null,
            'state' => isset($row['State']) ? $row['State'] : null,
            'county' => isset($row['County']) ? $row['County'] : null,
            'city' => isset($row['Municipality']) ? $row['Municipality'] : null,
            'status' => isset($row['Status']) ? $row['Status'] : null,
            'tier' => isset($row['Tier']) ? $row['Tier'] : null,
            'typist_qc_id' => isset($row['Typist QC']) ? $row['Typist QC'] : null,
            'typist_id' => isset($row['Typist']) ? $row['Typist'] : null,
            'audit_id' => $this->auditId,
            'created_by' => $this->userid,
        ];



        if (!$order_date ) {
            $data['comments'] = 'Invalid Date Format';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $stateCode = isset($row['State']) ? trim($row['State']) : null;
        if ($stateCode) {
        $state = State::where('short_code', $stateCode)->first();
        }
        $countyName = isset($row['County']) ? trim($row['County']) : null;
        if ($countyName) {
        if ($state) {
            $county = County::where('county_name', $countyName)->where('stateId', $state->id)->first();
        }
        }

        $municipality = isset($row['Municipality']) ? trim($row['Municipality']) : null;

        if ($municipality) {
        if ($county) {
            $city = City::where('city', $municipality)->where('county_id', $county->id)->first();

        if (!$city) {
            $data['comments'] = 'Municipality not matched with database records';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }
        }
        }
        
        $process = trim($row['Product Name']);
        if (!$process) {
            $data['comments'] = 'Product Name should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        } else {
            $process = Process::whereRaw('LOWER(process_name) = ?', strtolower($process))->first();
            if (!$process) {
                $data['comments'] = 'Product Name not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }


        $client_id = trim($row['Client']);
         if(!$client_id) {
            $data['comments'] = 'Client should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        } else {
            $client_id = Client::whereRaw('LOWER(client_name) = ?', [strtolower($client_id)])->first();
            if (!$client_id) {
                $data['comments'] = 'Client not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }



       
      
        //Lob
        $lob = trim($row['Lob']);
        
        if (!$lob) {
            $data['comments'] = 'Lob should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        } else {
            $client = trim($row['Client']);
            $client = Client::whereRaw('LOWER(client_name) = ?', [strtolower($client)])->first();
            // $lob = Lob::whereRaw('LOWER(name) = ?', [strtolower($lob)])->where('client_id', $client->id)->first();

            $lob = Lob::whereRaw('LOWER(name) = ?', [strtolower($lob)])
            ->whereRaw('JSON_CONTAINS(client_id, ?)', [json_encode((string)$client->id)])
            ->first();

            if (!$lob) {
                $data['comments'] = 'Lob not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }


            $process_type = trim($row['Process']);
            if ($process_type) {
            $process = trim($row['Product Name']);
            $client = trim($row['Client']);
            $client_name = Client::whereRaw('LOWER(client_name) = ?', [strtolower($client)])->first();

            // $client_name = Process::where('client_id', $client->id);

                $stl_process = stlprocess::where('name', $process_type)
                    ->where('lob_id', $lob->id) 
                    ->first();
            
                if ($stl_process) {
            
                    $process_typeid = Process::leftJoin('stl_lob', 'stl_lob.id', '=', 'stl_item_description.lob_id')
                        ->leftJoin('stl_process', 'stl_process.id', '=', 'stl_item_description.process_id')
                        ->where('stl_item_description.process_id', $stl_process->id)
                        ->where('stl_item_description.lob_id', $lob->id)
                        ->where('stl_item_description.process_name', $process)
                        ->where('stl_item_description.client_id', $client_name->id)
                        ->select('stl_item_description.id', 'stl_item_description.process_name')
                        ->first();
                        // dd($process_typeid);
                    
                    if ($process_typeid) {
                        $process_type_id = $process_typeid->id;
                        // dd($process_type_id);
                    } else {
                        $data['comments'] = 'Process not matched with database records';
                        OrderTemp::insert($data);
                        ++$this->unsuccess_rows;
                        return null;
                    }
                } else {
                    $data['comments'] = 'Process not found in database';
                    OrderTemp::insert($data);
                    ++$this->unsuccess_rows;
                    return null;
                }
            } else {
                $data['comments'] = 'Process should not be empty';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
            




        $Status = trim($row['Status']);
        if (!$Status) {
            $data['comments'] = 'Status should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        if ($Status == 'WIP') {
            $statusRecord = Status::where('status', $Status)->first();

            if ($statusRecord) {
                $Status = $statusRecord->id;
        } else {
                $data['comments'] = 'The status WIP does not match the records in the database';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        } else {
            $data['comments'] = 'The status is not WIP';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $assignee_user = isset($row['Emp ID-Order Assigned']) ? trim($row['Emp ID-Order Assigned']) : null;

        if ($assignee_user) {
            $assignee_user = User::where('emp_id', $assignee_user)->whereIn('user_type_id', [6,8,9])->first();
        }

        $assignee_qa = isset($row['Assignee_QA']) ? trim($row['Assignee_QA']) : null;

        if ($assignee_qa) {
            $assignee_qa = User::where('emp_id', $assignee_qa)->whereIn('user_type_id', [7,8])->first();
        }

        if(isset($row['Typist QC'])){
    $TypistQC = trim($row['Typist QC']);
    $TypistQC = User::where('emp_id', $TypistQC)->first();
    if (!$TypistQC) {
        $data['comments'] = 'Typist QC not matched with database records';
        OrderTemp::insert($data);
        ++$this->unsuccess_rows;
        return null;
    }
        }

        if(isset($row['Typist'])){
    $Typist = trim($row['Typist']);
    $Typist = User::where('emp_id', $Typist)->first();
    if (!$Typist) {
        $data['comments'] = 'Typist not matched with database records';
        OrderTemp::insert($data);
        ++$this->unsuccess_rows;
        return null;
    }
        }

        $processOrg = trim($row['Product Name']);
        if ($processOrg) {

            $process_type = trim($row['Process']);
            $client = trim($row['Client']);

            $client_name = Client::whereRaw('LOWER(client_name) = ?', [strtolower($client)])->first();


                    $stl_process = stlprocess::where('name', $process_type)
                    ->where('lob_id', $lob->id) 
                    ->first();

                $process_orgid = Process::leftJoin('stl_lob', 'stl_lob.id', '=', 'stl_item_description.lob_id')
                ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
                    ->where('stl_item_description.lob_id', $lob->id)
                    ->where('stl_item_description.process_name', $processOrg)
                    ->where('stl_item_description.process_id', $stl_process->id)
                    ->where('stl_item_description.client_id', $client_name->id)
                    ->select('stl_item_description.id', 'stl_item_description.process_name', 'stl_item_description.process_id')
                    ->first();
                
                if ($process_orgid) {
                    $processOrg = $process_orgid;
                } else {
                    $data['comments'] = 'Process not matched with database records';
                    OrderTemp::insert($data);
                    ++$this->unsuccess_rows;
                    return null;
                }
           
        } else {
            $data['comments'] = 'Process should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }




        if (isset($row['Tier'])){
            $Tier = trim($row['Tier']);
            $process_type_name = trim($row['Process']);
            $process_type_name = stlprocess::whereRaw('LOWER(name) = ?', [strtolower($process_type_name)])->first();
            $Tier = Tier::where('tier_id', $Tier)
            ->whereRaw('JSON_CONTAINS(stl_process_id, ?)', [json_encode((string)$process_type_name->id)])
            ->first();
            
            if (!$Tier) {
                $data['comments'] = 'Tier not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }

        $orderDate_condition = \Carbon\Carbon::parse($data['order_date'])->format('Y-m-d');

        $existingOrder = OrderCreation::where('order_id', $data['order_id'])
                    ->whereDate('order_date', '=', $orderDate_condition)
                    ->where('process_id', $processOrg->id)
                    ->where('lob_id',  $lob->id)
                    ->where('process_type_id', $process_typeid->process_id)
                    ->where('status_id', '!=', 3)
                    ->exists();
        


        if ($existingOrder) {
            $data['comments'] = 'Duplicate Order ID and Order Date found';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $order_date = isset($order_date) ? date('Y-m-d H:i:s', strtotime($order_date)) : null;

        $currentTimeIST = Carbon::now();
        if ($order_date > ($currentTimeIST)) {
            $data['comments'] = 'Future Date and Time not Allowed';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $processOrg = trim($row['Product Name']);
        if ($processOrg) {

            $process_type = trim($row['Process']);
            $client = trim($row['Client']);
            $client_name = Client::whereRaw('LOWER(client_name) = ?', [strtolower($client)])->first();


                    $stl_process = stlprocess::where('name', $process_type)
                    ->where('lob_id', $lob->id) 
                    ->first();

                $process_orgid = Process::leftJoin('stl_lob', 'stl_lob.id', '=', 'stl_item_description.lob_id')
                ->leftJoin('stl_process', 'stl_item_description.process_id', '=', 'stl_process.id')
                    ->where('stl_item_description.lob_id', $lob->id)
                    ->where('stl_item_description.process_name', $processOrg)
                    ->where('stl_item_description.process_id', $stl_process->id)
                    ->where('stl_item_description.client_id', $client_name->id)
                    ->select('stl_item_description.id', 'stl_item_description.process_name', 'stl_item_description.process_id')
                    ->first();
                
                if ($process_orgid) {
                    $processOrg = $process_orgid;
                } else {
                    $data['comments'] = 'Process not matched with database records';
                    OrderTemp::insert($data);
                    ++$this->unsuccess_rows;
                    return null;
                }
           
        } else {
            $data['comments'] = 'Process should not be empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }
          
       



            $process_type = trim($row['Process']);
            if ($process_type) {
            $process = trim($row['Product Name']);

                $stl_process = stlprocess::where('name', $process_type)
                    ->where('lob_id', $lob->id) 
                    ->first();
            
                if ($stl_process) {

                    $process_typeid = Process::leftJoin('stl_lob', 'stl_lob.id', '=', 'stl_item_description.lob_id')
                        ->leftJoin('stl_process', 'stl_process.id', '=', 'stl_item_description.process_id')
                        ->where('stl_item_description.process_id', $stl_process->id)
                        ->where('stl_item_description.lob_id', $lob->id)
                        ->where('stl_item_description.process_name', $process)
                        ->select('stl_item_description.process_id', 'stl_item_description.process_name')
                        ->first();
                    
                    if ($process_typeid) {
                        $process_type_id = $process_typeid;
                    } else {
                        $data['comments'] = 'Process not matched with database records';
                        OrderTemp::insert($data);
                        ++$this->unsuccess_rows;
                        return null;
                    }
                } else {
                    $data['comments'] = 'Process not found in database';
                    OrderTemp::insert($data);
                    ++$this->unsuccess_rows;
                    return null;
                }
            } else {
                $data['comments'] = 'Process should not be empty';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }

            

        if (!$processOrg) {
            $data['comments'] = 'Product Name not matched with database records for the given Lob';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $orderDate_condition = \Carbon\Carbon::parse($data['order_date'])->format('Y-m-d');

        $existingOrder = OrderCreation::where('order_id', $data['order_id'])
                    ->whereDate('order_date', '=', $orderDate_condition)
                    ->where('process_id', $processOrg->id)
                    ->where('lob_id',  $lob->id)
                    ->where('process_type_id', $process_typeid->process_id)
                    ->exists();

 

 
         if ($existingOrder) {
             $data['comments'] = 'Duplicate Order ID and Order Date or Process found';
             OrderTemp::insert($data);
             ++$this->unsuccess_rows;
             return null;
         }
 
         $order_date = isset($order_date) ? date('Y-m-d H:i:s', strtotime($order_date)) : null;
 
         $currentTimeIST = Carbon::now();
         if ($order_date > ($currentTimeIST)) {
             $data['comments'] = 'Future Date and Time not Allowed';
             OrderTemp::insert($data);
             ++$this->unsuccess_rows;
             return null;
         }


        try {
            $orderId = isset($row['OrderID']) ? $row['OrderID'] : null;

            // Insert the new record with insertOrIgnore
            $orderId = OrderCreation::insert([
                'order_date' => $order_date,
                'order_id' => $orderId,
                'client_id' => $client_id->id ?? null,
                'process_id' => $processOrg->id,
                'state_id' => isset($state) ? $state->id : null,
                'county_id' => isset($county) ? $county->id : null,
                'city_id' => isset($city) ? $city->id : null,
                'status_id' => $Status,
                'assignee_user_id' => isset($assignee_user->id) ? $assignee_user->id : null,
                'assignee_qa_id' => isset($assignee_qa->id) ? $assignee_qa->id : null,
                'created_by' => $this->userid,
                'typist_id' => isset($Typist) ? $Typist->id : null,
                'typist_qc_id' =>isset($TypistQC) ? $TypistQC->id : null,
                'process_type_id' => $process_type_id->process_id, // Use the id
                'tier_id' => $Tier->id ?? null,
                'lob_id' => $lob->id ?? null,
                'status_updated_time' => Carbon::now(),
            ]);

            if (!$orderId) {
                $data['comments'] = 'Duplicate Order ID found';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }

            ++$this->success_rows;
            return null;
        } catch (\Exception $e) {
            ++$this->unsuccess_rows;
            return null;
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
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
                $oldData = OrderCreationAudit::lockForUpdate()->find($this->auditId);
                OrderCreationAudit::where('id', $oldData->id)->update([
                    'successfull_rows' => $oldData->successfull_rows + $this->success_rows,
                    'unsuccessfull_rows' => $oldData->unsuccessfull_rows + $this->unsuccess_rows,
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                Log::error("Error updating OrderCreationAudit: " . $e->getMessage());
            }
        }
    }
}
