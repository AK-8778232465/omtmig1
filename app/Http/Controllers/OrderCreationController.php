<?php

namespace App\Http\Controllers;

use App\Exports\FailedOrdersExport;
use App\Imports\OrdersCreationImport;
use App\Models\County;
use App\Models\OrderCreation;
use App\Models\OrderCreationAudit;
use App\Models\OrderTemp;
use App\Models\State;
use App\Models\Status;
use App\Models\User;
use App\Models\Product;
use App\Models\Tier;
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
        $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->whereIn('id', $processIds)->select('id', 'process_name', 'project_code')->get();
        $stateList = State::select('id', 'short_code')->get();
        $processors = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [6,8])->get();
        $qcers = User::select('id', 'username', 'emp_id', 'user_type_id')->where('is_active', 1)->whereIn('user_type_id', [7,8])->get();
        $statusList = Status::select('id', 'status')->get();
        $countyList = County::select('id', 'county_name')->get();
        $exceldetail = OrderCreationAudit::with('users')->orderBy('created_at', 'desc')->get();

        $tierList = Tier::select('id','Tier_id')->get();

        return view('app.orders.ordercreate', compact('processList', 'stateList', 'statusList', 'processors', 'qcers', 'countyList','exceldetail','tierList'));
    }

    public function getlob(Request $request){

        $query = DB::table('oms_order_creations')
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->leftJoin('oms_products', 'stl_item_description.client_id', '=', 'oms_products.client_id')
            ->where('stl_item_description.id', $request->process_id)->pluck('oms_products.lob_id')
            ->toArray();
            $lobData = DB::table('stl_lob')->whereIn('id',array_unique($query))->get();
        return response()->json($lobData);
    }

    public function getproduct(Request $request) {
        $productList = Product::where('lob_id', $request->lob_id)
            ->where('is_active', 1)
            ->get();
            
        return response()->json($productList);
    }
    

    public function exportFailedOrders($audit_id)
    {
        $failedOrders = OrderTemp::where('audit_id', $audit_id)->where('created_by', Auth::id())->get();

        $export = new FailedOrdersExport($failedOrders);
        $exportFileName = 'failed_orders_export_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $exportFileName);
    }

    public function InsertOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'order_date' => 'required',
            'process_code' => 'required',
            'order_status' => 'required',
        ]);

        $input = $request->all();
        $orderData = [
            'order_id' => $input['order_id'],
            'order_date' => $input['order_date'],
            'process_id' => $input['process_code'],
            'state_id' => isset($input['property_state']) ? $input['property_state'] : NULL,
            'county_id' => isset($input['property_county']) ? $input['property_county'] : NULL,
            'status_id' => $input['order_status'],
            'assignee_user_id' => isset($input['assignee_user']) ? $input['assignee_user'] : NULL,
            'assignee_qa_id' => isset($input['assignee_qa']) ? $input['assignee_qa'] : NULL,
            'lob_id' => isset($input['lob_id']) ? $input['lob_id'] : NULL,
            'product_id' => isset($input['product_id']) ? $input['product_id'] : NULL,
            'tier_id' => isset($input['tier_id']) ? $input['tier_id'] : NULL,
            'created_by' => Auth::id(),
        ];

        $duplicateOrderCount = OrderCreation::where('process_id', $input['process_code'])
            ->where('order_id', $input['order_id'])
            ->where('is_active', 1)
            ->count();

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

    public function delete_order(Request $request)
    {
        $DeleteOrder = OrderCreation::where('id', $request->id)->update(['is_active' => 0]);

        return response()->json(['data' => 'success','msg' => 'Deleted Successfully']);
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
