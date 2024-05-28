<?php

namespace App\Imports;

use App\Models\County;
use App\Models\OrderCreationAudit;
use App\Models\OrderCreation;
use App\Models\OrderTemp;
use App\Models\Process;
use App\Models\State;
use App\Models\Status;
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
            $order_date = date('Y-m-d H:i:s', strtotime('1899-12-30') + ($orderDateValue * 86400));
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
            'assignee_user' => isset($row['Emp ID-Order Assigned']) ? $row['Emp ID-Order Assigned'] : null,
            'assignee_qa' => isset($row['Assignee_QA']) ? $row['Assignee_QA'] : null,
            'process' => isset($row['Process']) ? $row['Process'] : null,
            'state' => isset($row['State']) ? $row['State'] : null,
            'county' => isset($row['County']) ? $row['County'] : null,
            'status' => isset($row['Status']) ? $row['Status'] : null,
            'created_by' => $this->userid,
            'audit_id' => $this->auditId,
        ];

        if (!$order_date) {
            $data['comments'] = 'Invalid Data Format';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $stateCode = trim($row['State']);
        $countyName = trim($row['County']);

        $state = State::where('short_code', $stateCode)->first();
        if ($state) {
            $county = County::where('county_name', $countyName)->where('stateId', $state->id)->first();
        }


        $process = trim($row['Process']);

        if (!$process) {
            $data['comments'] = 'Process should not empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        } else {
            $process = Process::whereRaw('LOWER(process_name) = ?', strtolower($process))->first();
            if (!$process) {
                $data['comments'] = 'Process not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }

        // Status
        $Status = trim($row['Status']);
        if (!$Status) {
            $data['comments'] = 'Status should not empty';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        } else {
            $Status = Status::where('status', $Status)->first();
            if (!$Status) {
                $data['comments'] = 'Status not matched with database records';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        }


        //  Assignee
        $assignee_user = trim($row['Emp ID-Order Assigned']);
        if ($assignee_user) {
            $assignee_user = User::where('emp_id', $assignee_user)->whereIn('user_type_id', [6,8])->first();
        }


        $assignee_qa = trim($row['Assignee_QA']);
        if ($assignee_qa) {
            $assignee_qa = User::where('emp_id', $assignee_qa)->whereIn('user_type_id', [7,8])->first();
        }

        if($Status->id == 5) {
            if($process->qc_enabled && (!$assignee_user || !$assignee_qa)) {
                $data['comments'] = 'User and QA should not empty for Completed orders';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            } elseif(!$assignee_user) {
                $data['comments'] = 'User should not empty for Completed orders';
                OrderTemp::insert($data);
                ++$this->unsuccess_rows;
                return null;
            }
        } elseif($Status->id != 1 && !$assignee_user) {
            $data['comments'] = 'User should only empty on WIP orders';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        $existingOrder = OrderCreation::where('order_id', $data['order_id'])
        ->where('order_date', $data['order_date'])
        ->exists();
 
        // If the order already exists, handle it accordingly
        if ($existingOrder) {
            $data['comments'] = 'Duplicate Order ID and Order Date found';
            OrderTemp::insert($data);
            ++$this->unsuccess_rows;
            return null;
        }

        try {
            $orderId = isset($row['OrderID']) ? $row['OrderID'] : null;

            // Insert the new record with insertOrIgnore
            $orderId = OrderCreation::insertOrIgnore([
                'order_date' => $order_date,
                'order_id' => $orderId,
                'process_id' => $process->id,
                'state_id' => isset($state) ? $state->id : null,
                'county_id' => isset($county) ? $county->id : null,
                'status_id' => $Status->id,
                'assignee_user_id' => isset($assignee_user->id) ? $assignee_user->id : null,
                'assignee_qa_id' => isset($assignee_qa->id) ? $assignee_qa->id : null,
                'created_by' => $this->userid,
            ]);

            if (!$orderId) {
                // Duplicate entry found
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
