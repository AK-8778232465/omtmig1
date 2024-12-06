<?php

namespace App\Http\Controllers;

use App\Exports\FailedOrdersExport;
use App\Imports\OrdersCreationImport;
use App\Models\County;
use App\Models\OrderCreation;
use App\Models\OrderCreationAudit;
use App\Models\OrderTemp;
use App\Models\State;
use App\Models\City;
use App\Models\Status;
use App\Models\User;
use App\Models\Product;
use App\Models\Tier;
use App\Models\Lob;
use App\Models\Client;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderCreationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function single_order(Request $request)
    {

        $user = User::where('id', Auth::id())->first();

        $processList=[];
        session(['user_type_id' => $user->user_type_id]);
        $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
        if (Auth::user()->hasRole('Super Admin')) {
            $processIds = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
        } else {
            $processIds = DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
    
        }
        $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->whereIn('id', $processIds)->select('id', 'process_name', 'project_code', 'client_id')->orderBy('project_code')->get();
        $stateList = State::select('id', 'short_code')->get();
        $processors = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [6, 8, 9])->orderBy('emp_id')->get();
        $qcers = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [7, 8])->orderBy('emp_id')->get();
        $statusList = Status::select('id', 'status')->get();
        $countyList = County::select('id', 'county_name')->get();
        $exceldetail = OrderCreationAudit::with('users')->orderBy('created_at', 'desc')->get();

        $tierList = Tier::select('id','Tier_id')->get();
        $typists = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [10,22])->get();
        $typist_qcs = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [11,22])->get();

        $mapped_lobs = DB::table('oms_user_service_mapping')->where('user_id', $user->id)->where('is_active', 1)->pluck('service_id')->toArray();
        
    

        $clients = DB::table('stl_client')
        ->leftjoin('stl_item_description', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->where('stl_item_description.is_approved', 1)
        ->where('stl_item_description.is_active', 1)
        ->whereIn('stl_item_description.id', $mapped_lobs)
        ->select('stl_item_description.client_id','stl_client.id', 'stl_client.client_name')
        ->distinct()
        ->get();


        return view('app.orders.ordercreate', compact('processList', 'stateList', 'statusList', 'processors', 'qcers', 'countyList','exceldetail','tierList','typists','typist_qcs', 'clients'));
    }

public function getlobid(Request $request){
    $client = $request->input('select_client_id');

    $user = User::where('id', Auth::id())->first();
    $mapped_lobs = DB::table('oms_user_service_mapping')->where('user_id', $user->id)->where('is_active', 1)->pluck('service_id')->toArray();

    $lobs = DB::table('stl_item_description')
        ->leftjoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->where('stl_item_description.is_approved', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.client_id', $client)
        ->whereIn('stl_item_description.id', $mapped_lobs)
        ->select(
            'stl_lob.name as name',
            'stl_lob.client_id as client_id',
            'stl_lob.id as id',
           
        )
        ->distinct()
        ->get();
        return response()->json($lobs);

   
}

    
    public function getprocesstypeid(Request $request)
    {
        $lob = $request->input('lob_id');
        $client = $request->input('select_client_id');

 
        $user = User::where('id', Auth::id())->first();
       
        $processIds = DB::table('oms_user_service_mapping')->where('user_id', $user->id)->where('is_active', 1)->pluck('service_id')->toArray();
   
 
        $processtype = DB::table('stl_item_description')
        ->leftjoin('stl_process', 'stl_process.id', '=', 'stl_item_description.process_id')
        ->leftjoin('stl_client', 'stl_client.id', '=', 'stl_item_description.client_id')
            ->select('stl_process.id', 'stl_process.name')
            ->whereIn('stl_item_description.id', $processIds)
            ->where('stl_process.lob_id', $lob)
            ->where('stl_item_description.lob_id', $lob)
            ->where('stl_item_description.client_id', $client)
            ->groupBy('stl_process.id')
            ->get();
        return response()->json($processtype);
    }


    public function getprocess_code(Request $request)
    {
 
        $user = User::where('id', Auth::id())->first();
 
        $processIds = DB::table('oms_user_service_mapping')->where('user_id', $user->id)->where('is_active', 1)->pluck('service_id')->toArray();
 
        $lob = $request->input('lob_id');
        $client = $request->input('select_client_id');
       
        $process_type_id = $request->input('process_type_id');
        $process_code = DB::table('stl_item_description')->select('id', 'process_name', 'project_code', 'process_id')->where('client_id', $client)->where('lob_id', $lob)->where('process_id', $process_type_id)->whereIn('id', $processIds )->get();

    $get_tier = DB::table('oms_tier')
        ->select('id', 'Tier_id')
        ->where(function($query) use ($process_type_id) {
            $query->where('stl_process_id', 'LIKE', '%"'.$process_type_id.'"%');
        })
        ->get();

    return response()->json([
        'process_code' => $process_code,
        'tiers' => $get_tier
    ]);
 
 
    }






    
    public function getCities(Request $request)
    {
        $cities = City::select('id', 'city')->where('county_id', $request->county_id)->get();
        
        return response()->json($cities);
    }
    

    public function exportFailedOrders($audit_id)
    {
        $failedOrders = OrderTemp::where('audit_id', $audit_id)->get();

        $export = new FailedOrdersExport($failedOrders);
        $exportFileName = 'failed_orders_export_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $exportFileName);
    }

    public function InsertOrder(Request $request)
    {
        $request->validate([
            'select_client_id' => 'required',
            'order_id' => 'required',
            'order_date' => 'required',
            'process_code' => 'required',
            'order_status' => 'required',
        ]);

        $input = $request->all();
        $orderData = [
            'order_id' => $input['order_id'],
            'order_date' => $input['order_date'],
            'client_id' => $input['select_client_id'],
            'process_id' => $input['process_code'],
            'state_id' => isset($input['property_state']) ? $input['property_state'] : NULL,
            'county_id' => isset($input['property_county']) ? $input['property_county'] : NULL,
            'city_id' => isset($input['city']) ? $input['city'] : NULL,
            'status_id' => $input['order_status'],
            'assignee_user_id' => isset($input['assignee_user']) ? $input['assignee_user'] : NULL,
            'assignee_qa_id' => isset($input['assignee_qa']) ? $input['assignee_qa'] : NULL,
            'lob_id' => isset($input['lob_id']) ? $input['lob_id'] : NULL,
            'process_type_id' => isset($input['process_type_id']) ? $input['process_type_id'] : NULL,
            'tier_id' => isset($input['tier_id']) ? $input['tier_id'] : NULL,
            'typist_id' => isset($input['typist_id']) ? $input['typist_id'] : NULL,
            'typist_qc_id' => isset($input['typist_qc_id']) ? $input['typist_qc_id'] : NULL,
            'created_by' => Auth::id(),
            'status_updated_time' => Carbon::now()
        ];




                if (in_array($input['process_type_id'], [2, 4, 6, 8, 9, 16])) {
                    $duplicateOrderCount = OrderCreation::where('order_id', $input['order_id'])
                                            ->whereIn('process_type_id', [2, 4, 6, 8, 9, 16])
                                            ->where('status_id', '!=', 3)
                                            ->where('is_active', '!=', 0);
                }else{
                    $duplicateOrderCount = OrderCreation::where('order_id', $input['order_id'])
                    ->where(DB::raw('DATE(order_date)'), '=', \Carbon\Carbon::parse($input['order_date'])->format('Y-m-d'))
                        ->where('client_id', $input['select_client_id'])
                        ->where('lob_id', $input['lob_id'])
                        ->where('process_type_id', $input['process_type_id'])
                        ->where('process_id', $input['process_code'])
                        ->where('is_active', 1)
                        ->where('status_id', '!=', 3)
                        ->where('is_active', '!=', 0);
                }

                $duplicateOrderCount = $duplicateOrderCount->count();

        if ($duplicateOrderCount > 0) {
            return response()->json(['data' => 'error', 'msg' => 'Order already exists.']);
        }

        if (OrderCreation::insert($orderData)) {
            return response()->json(['data' => 'success', 'msg' => 'Order Created Successfully!']);
        } else {
            return response()->json(['data' => 'error', 'msg' => $validator->errors()->all()]);
        }
    }

    public function orderStatus($id)
    {

        $order = OrderCreation::where('id', $id)->first();

        if ($order->is_active == 1) {
            OrderCreation::where('id', $id)->update(['is_active' => 0]);
        } else {
            OrderCreation::where('id', $id)->update(['is_active' => 1]);
        }

        return response()->json(['message' => 'Status Changed Successfully!']);

    }

    public function OrderCreationsImport(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');

        if ($file && $file->getClientOriginalExtension() == 'xlsx' && $file->isValid()) {
            $filename = 'exceldata_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('Uploaded_Excel_Files', $filename);

            $original_file_name = $file->getClientOriginalName();

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(storage_path('app/Uploaded_Excel_Files/' . $filename));
            $output = str_ireplace('.xlsx', '', $filename);
            $worksheet = $spreadsheet->getActiveSheet();
            // $totalRowCount = $worksheet->getHighestRow() - 1;


            $totalRowCount = -1;

            foreach ($worksheet->getRowIterator() as $row) {
                // Initialize a flag to check if any cell in the row is non-empty
                $nonEmptyRow = false;
                foreach ($row->getCellIterator() as $cell) {
                    // Check if the cell is not empty
                    if (!is_null($cell->getValue()) && $cell->getValue() !== '') {
                        $nonEmptyRow = true;
                        break; // Exit loop if any non-empty cell is found
                    }
                }
                // If any non-empty cell is found in the row, increment the row count
                if ($nonEmptyRow) {
                    $totalRowCount++;
                }
            }
            

            if (Auth::user()->hasRole('Super Admin') && $totalRowCount >= 4000) {
                $splitSize = ($totalRowCount/8);
            } elseif (Auth::user()->hasRole('AVP/VP') && $totalRowCount >= 4000) {
                $splitSize = ($totalRowCount/4);
            } elseif (Auth::user()->hasRole('Business Head') && $totalRowCount >= 3000) {
                $splitSize = ($totalRowCount/3);
            } elseif (Auth::user()->hasRole('PM/TL') && $totalRowCount > 2000) {
                $splitSize = ($totalRowCount/2);
            } else {
                $splitSize = ($totalRowCount/1);
            }

            $fileCount = 1;
            $rowCount = 1;
            $newSpreadsheet = new Spreadsheet();

            foreach ($worksheet->getRowIterator() as $row) {
                if ($row->getRowIndex() > 1) { // Skip the heading row
                    $cellIterator = $row->getCellIterator();
                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                    $newSpreadsheet->getActiveSheet()->fromArray([$rowData], null, 'A' . ++$rowCount);

                    if ($rowCount >= $splitSize + 1) {
                        // Add header row
                        $headerRow = $worksheet->getRowIterator(1)->current()->getCellIterator();
                        $headerData = [];
                        foreach ($headerRow as $cell) {
                            $headerData[] = $cell->getValue();
                        }
                        $newSpreadsheet->getActiveSheet()->fromArray([$headerData], null, 'A1');

                        $writer = new Xlsx($newSpreadsheet);
                        $writer->save(storage_path('app/Uploaded_Excel_Files/' . $output . '_' . str_pad($fileCount++, 4, '0', STR_PAD_LEFT) . '.xlsx'));

                        $rowCount = 1;
                        $newSpreadsheet = new Spreadsheet();
                    }
                }
            }

            // Save the remaining data with header row
            if ($rowCount > 1) {
                // Add header row
                $headerRow = $worksheet->getRowIterator(1)->current()->getCellIterator();
                $headerData = [];
                foreach ($headerRow as $cell) {
                    $headerData[] = $cell->getValue();
                }
                $newSpreadsheet->getActiveSheet()->fromArray([$headerData], null, 'A1');

                $writer = new Xlsx($newSpreadsheet);
                $writer->save(storage_path('app/Uploaded_Excel_Files/' . $output . '_' . str_pad($fileCount++, 4, '0', STR_PAD_LEFT) . '.xlsx'));
            }

            // Dispatch job for each split XLSX file
            $outputFilesPath = storage_path('app/Uploaded_Excel_Files/' . $output . '_*.xlsx');
            $auditId = OrderCreationAudit::insertGetId([
                'file_name' => $original_file_name,
                'total_rows' => $totalRowCount,
                'created_at' => now(),
                'created_by' => Auth::id()
            ]);

            if (file_exists(storage_path('app/Uploaded_Excel_Files/' . $filename))) {
                unlink(storage_path('app/Uploaded_Excel_Files/' . $filename));
            }

            foreach (glob($outputFilesPath) as $file) {
                Excel::import(new OrdersCreationImport(Auth::id(), $auditId), $file);
            }

            return response()->json(['success' => 'Order Inserted Successfully!', 'bacthId' => $auditId]);
        } else {
            return response()->json(['error' => 'The file does not exist, is not readable, or is not an XLSX file']);
        }
    }

    public function changeFolderPermissions($path, $permissions)
    {
        if (file_exists($path) || is_dir($path)) {
            chmod($path, $permissions);
            $items = scandir($path);

            foreach ($items as $item) {
                if ($item != '.' && $item != '..') {
                    $itemPath = $path.'/'.$item;
                    if (is_dir($itemPath)) {
                        $this->changeFolderPermissions($itemPath, $permissions);
                    } else {
                        chmod($itemPath, $permissions);
                    }
                }
            }
        }
    }

    public function edit_order(Request $request)
    {
        $orderDetail = OrderCreation::where('id', $request->id)->first();

        return response()->json($orderDetail);
    }

    public function updateOrder(Request $request)
    {

        $input = $request->all();

        $order = OrderCreation::find($input['order_id']);
        $isactive = $request->has('is_active') ? 1 : 0;

        $orderData = [
            'order_date' => $input['order_date'],
            'process_id' => $input['process_code'],
            'state_id' => $input['property_state'] ?? NULL,
            'county_id' => $input['property_county'] ?? NULL,
            'assignee_user_id' => $input['assign_user'] ?? NULL,
            'assignee_qa_id' => $input['assign_qa'] ?? NULL,

        ];

        $res = OrderCreation::where('id', $input['id'])->update($orderData);

        if ($res) {
            return response()->json(['data' => 'success', 'msg' => 'Order Updated Successfully!']);
        } else {
            return response()->json(['data' => 'error', 'msg' => 'Failed to update Order']);
        }
    }

public function unassign_user(Request $request)
{
    $orderId = $request->input('order_id');

    $order = OrderCreation::find($orderId);
    if ($order) {
        $order->assignee_user_id = null;// Unassign the user
        $order->status_id = 1;

        $order->save();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
}


public function unassign_qcer(Request $request)
{
    $orderId = $request->input('order_id');

    $order = OrderCreation::find($orderId);
    if ($order) {
        $order->assignee_qa_id = null; // Unassign the user
        $order->save();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
}

    public function delete_order(Request $request)
{
    try {
        if (!empty($request->orders)) {
            $DeleteOrder = OrderCreation::whereIn('id', $request->orders)
                ->update(['is_active' => 0]);

            if ($DeleteOrder) {
                return response()->json(['data' => 'success', 'msg' => 'Orders deleted successfully']);
            } else {
                return response()->json(['data' => 'error', 'msg' => 'No orders found to delete'], 404);
    }
        }
        elseif ($request->has('id')) {
            $DeleteOrder = OrderCreation::where('id', $request->id)
                ->update(['is_active' => 0]);

            if ($DeleteOrder) {
                return response()->json(['data' => 'success', 'msg' => 'Order deleted successfully']);
            } else {
                return response()->json(['data' => 'error', 'msg' => 'Order not found to delete'], 404);
            }
        } else {
            return response()->json(['data' => 'error', 'msg' => 'Invalid request data'], 400);
        }
    } catch (\Exception $e) {
        return response()->json(['data' => 'error', 'msg' => 'Failed to delete orders. Please try again.'], 500);
    }
}


    public function progressBar(Request $request)
    {
        try {
            $batchId = $request->id ?? session()->get('lastBatchId');
            if (DB::table('oms_order_creation_audit')->where('id', $batchId)->count()) {
                $response = DB::table('oms_order_creation_audit')->where('id', $batchId)->first();
                return response()->json($response);
            }
        } catch (\Throwable $e) {
            Log::error($e);
            dd($e);
        }
    }
}
