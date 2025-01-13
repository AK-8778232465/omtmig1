<?php

namespace App\Http\Controllers;
use App\Models\docs;
use App\Models\doctypes;
use App\Models\billingcurrency;
use App\Models\Client;
use App\Models\ClientSupportingDoc;
use App\Models\ClientType;
use App\Models\Company;
use App\Models\County;
use App\Models\Lob;
use App\Models\Order;
use App\Models\Process;
use App\Models\Product;
use App\Models\ProcessLocation;
use App\Models\Service;
use App\Models\Country;
use App\Models\ServiceUserMapping;
use App\Models\Location;
use App\Models\Unit_Type;
use App\Models\stlprocess;
use App\Models\State;
use App\Models\Status;
use App\Models\SupportingDocs;
use App\Models\User;
use App\Models\OmsUserProfile;
use App\Models\UserType;
use App\Models\CountyInstructionAudit;
use App\Models\CountyInstructionTemp;
use Carbon\Carbon;
use App\Models\Stl_User;

use App\Models\Service_Audit;
use App\Models\Line_Item;
use DB;
use Hash;
use App\Exports\FailedCIOrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Imports\AssignImport;
use App\Imports\SduploadImport;
use App\Exports\UserServiceMappingExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Session;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
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
    public function setting(Request $request)
    {
        if($request->is('settings/users') ||$request->is('settings') ){
            if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP') || Auth::user()->hasRole('SPOC') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('VP')) {
                $currentUserId = Auth::id();
                $user = User::find($currentUserId);

                $user_lower_ids = User::getAllLowerLevelUserIds_all($currentUserId);

                $user_lower_ids = array_filter($user_lower_ids, function($id) use ($currentUserId) {
                    return $id != $currentUserId;
                });

                $usersData = User::select(
                    'oms_users.id', 
                    'oms_users.emp_id', 
                    'oms_users.username', 
                    'oms_users.email', 
                    DB::raw("DATE_FORMAT(oms_users.created_at, '%m/%d/%Y') as created_date"), // Format the created_at field
                    'reporting_user.username as reporting_username', 
                    'roles.name as roles', 
                    'oms_users.is_active'
                )
                ->leftJoin('oms_user_profiles', 'oms_user_profiles.oms_user_id', '=', 'oms_users.id') // Join oms_user_profiles table
                ->leftJoin('oms_users as reporting_user', 'oms_user_profiles.reporting_to', '=', 'reporting_user.id') // Use reporting_to to get the reporting user's details
                ->leftJoin('roles as roles', 'oms_user_profiles.user_type_id', '=', 'roles.id') // Assuming roles are tied by role_id in oms_users table
                ->when(!in_array($user->user_type_id, [1, 23]), function ($query) use ($user_lower_ids) {
                    $query->whereIn('oms_users.id', $user_lower_ids);
                })
                ->when($user->user_type_id == 23, function ($query) {
                    $query->whereNotIn('oms_users.user_type_id', [1]);
                })
                ->get();

                            
                // $usersData = User::with('usertypes:id,usertype')->whereNotIn('user_type_id', [1,4])->get();
                $loggedInUserTypeId = $user->user_type_id;

                if($loggedInUserTypeId == 23)  {
                    $userTypes = UserType::where(function ($query) use ($loggedInUserTypeId) {
                        $query->where('id', '<>', $loggedInUserTypeId)
                              ->orWhere('id', '<=', $loggedInUserTypeId);
                    })
                        ->whereNotIn('id', [1, 4])
                        ->get();
                }elseif($loggedInUserTypeId == 24){
                    $userTypes = UserType::where('id','<', $loggedInUserTypeId)
                        ->whereNotIn('id', [1,4,23])
                        ->get();
                 }elseif($loggedInUserTypeId == 1){
                    $userTypes = UserType::where('id','>=', $loggedInUserTypeId)
                        ->whereNotIn('id', [1,4])
                        ->get();
                 }else{
                    $userTypes = UserType::where('id', '>', $loggedInUserTypeId)
                        ->whereNotIn('id', [1,4,23,24])
                        ->get();
                 }
                
                 $reportingUsers = User::all();


                $exportCount = ServiceUserMapping::count(); 
                $clients = DB::table('stl_client')
                ->select('id', 'client_name') // Select only the necessary fields
                ->get();
                $usertypes = DB::table('stl_usertype')
                ->select('id', 'usertype') // Select only necessary fields
                ->get();

                $lobs = DB::table('stl_item_description')
                ->leftjoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
                ->where('stl_item_description.is_approved', 1)
                ->where('stl_item_description.is_active', 1)
                ->select(
                    'stl_lob.name as name',
                    'stl_lob.client_id as client_id',
                    'stl_lob.id as id',
                   
                )
                ->distinct()
                ->get();
        
                $processes = DB::table('stl_process')
                        ->where('is_active', 1) // Only include active processes
                        ->orderBy('name', 'asc')
                        ->get();




                return view('app.settings.users', compact('usersData', 'userTypes','exportCount','clients','usertypes','reportingUsers','processes','lobs'));
            } else {
                abort(403);
            }
        }else if ($request->is('settings/products')) {

            if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('PM/TL') || Auth::user()->hasRole('Business Head') || Auth::user()->hasRole('AVP')|| Auth::user()->hasRole('Admin') || Auth::user()->hasRole('VP')) {
                $lobData = DB::table('stl_lob')->get();
                $clients = Client::select('id','client_no', 'client_name')->where('is_active', 1)->where('is_approved', 1)->get();
                $products = Product::all();
                $products = Product::with('client', 'lob')->get();

                // return response()->json($products);

                return view('app.settings.product',compact('lobData','clients','products'));
                }
        }else if ($request->is('settings/sduploads')){
            $clients = Client::select('id','client_no', 'client_name')->where('is_active', 1)->where('is_approved', 1)->orderBy('client_no')->get();
            $exceldetail = CountyInstructionAudit::with('users')->orderBy('created_at', 'desc')->get();
            return view('app.settings.sduploads',compact('clients', 'exceldetail'));

        }else if ($request->is('settings/geoinformations')){

            $stateList = State::select('id', 'short_code')->get();

            return view('app.settings.geoinformation',compact('stateList'));
        }
    }

    //Users
    public function addUsers(Request $request)
    {    
        $request->validate([
            'emp_id' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:oms_users,email',
            'password' => 'nullable|string|min:8',
            'contact_no' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',

        ]);
    
        // Check if the user already exists
        $check_users = User::where('emp_id', $request->emp_id)->first();
        if ($check_users) {
            return response()->json(['data' => 'error', 'msg' => 'User Already Exists!']);
        }
    
        // Prepare user data
        $usersData = [
            'emp_id' => trim(strtoupper($request->emp_id)),
            'username' => trim(strtoupper($request->username)),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->has('is_active') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => Auth::id(),
        ];
    
        // Insert user and get the ID
        $userId = User::insertGetId($usersData);
    
        foreach ($request->client_name as $index => $clientId) {
            // Get the lob_process string at the given index
            $lobProcess = isset($request->lob_process[$index]) ? $request->lob_process[$index] : null;
            
            // Split the lob_process string into two parts using explode() if it's not null
            $lobParts = $lobProcess ? explode(',', $lobProcess) : [null, null];
        
            // Create the OmsUserProfile record
            OmsUserProfile::create([
                'oms_user_id' => $userId,
                'client_id' => $clientId,
                'lob_id' => $lobParts[0], // First part of the lob_process (if exists)
                'process_id' => $lobParts[1], // Second part of the lob_process (if exists)
                'user_type_id' => isset($request->userRole[$index]) ? $request->userRole[$index] : null,
                'reporting_to' => isset($request->reporting_to[$index]) ? $request->reporting_to[$index] : null,
                'added_by' => Auth::id(),
            ]);
        }


        // If user creation is successful, proceed with additional data
        if ($userId) {
           
            // Assign role to the user
            $user = User::find($userId);
            $user->assignRole($request->user_type_id);
    
            return response()->json(['data' => 'success', 'msg' => 'User Added Successfully!']);
        }
    
        // If user creation fails, return an error
        return response()->json(['data' => 'error', 'msg' => 'Failed to add user. Please try again.']);
    }

    public function edit_user(Request $request)
    {
        $id = $request->id;
    
        // Fetch the user data
        $userDetail = DB::table('oms_users')
            ->select('oms_users.*')  // Only select user-related columns
            ->where('oms_users.id', $id)
            ->first();  // Get the first result since you're using 'id'
    
        // Check if user exists
        if (!$userDetail) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Fetch the related profile data
        $profileDetail = DB::table('oms_user_profiles')
            ->select('oms_user_profiles.id as profile_id', 'oms_user_profiles.client_id', 'oms_user_profiles.lob_id', 
                     'oms_user_profiles.process_id', 'oms_user_profiles.user_type_id', 'oms_user_profiles.reporting_to', 
                     'oms_user_profiles.added_by', 'oms_user_profiles.created_at')
            ->where('oms_user_profiles.oms_user_id', $userDetail->id)  // Use the oms_user_id from the user data
            ->get();  // Assuming one profile per user, adjust if needed
    
        // Combine the data into a single response
        $userDetail->profile = $profileDetail;
    // dd($userDetail);
        return response()->json($userDetail);
    }
    

    public function userStatus($userid)
    {
        $users = User::find($userid);
        if ($users->is_active == 1) {
            User::where('id', $userid)->update(['is_active' => 0]);
        } else {
            User::where('id', $userid)->update(['is_active' => 1]);
        }

        return redirect()->back()->with('success', 'Status Changed Successfully!');
    }

    public function updateUsers(Request $request)
    {

        $request->validate([
            'username' => 'required',
            'emp_id' => 'required',
        ]);
        $input = $request->all();
        $isactive = (isset($input['is_active_ed'])) ? 1 : 0;

        $usersData = [
            // 'user_type_id' => $input['user_type_id'],
            'emp_id' => trim(strtoupper($input['emp_id'])),
            'username' => trim(strtoupper($input['username'])),
            'email' => $input['email'],
            'password' => $input['password'],
            // 'reporting_to' => isset($input['reporting_to']) ? $input['reporting_to'] : null,
            'is_active' => $isactive,
            'updated_at' => now(),
        ];

        OmsUserProfile::where('oms_user_id', $input['user_id'])->delete();
    
        foreach ($request->client_name as $index => $clientId) {
            // Get the lob_process string at the given index
            $lobProcess = isset($request->lob_process[$index]) ? $request->lob_process[$index] : null;
            
            // Split the lob_process string into two parts using explode() if it's not null
            $lobParts = $lobProcess ? explode(',', $lobProcess) : [null, null];
        
            // Create the OmsUserProfile record
            OmsUserProfile::create([
                'oms_user_id' => $input['user_id'],
                'client_id' => $clientId,
                'lob_id' => $lobParts[0], // First part of the lob_process (if exists)
                'process_id' => $lobParts[1], // Second part of the lob_process (if exists)
                'user_type_id' => isset($request->userRole[$index]) ? $request->userRole[$index] : null,
                'reporting_to' => isset($request->reporting_to[$index]) ? $request->reporting_to[$index] : null,
                'added_by' => Auth::id(),
            ]);
        }

        $checkPass = User::where('id', $input['user_id'])->first();
        if ($checkPass->password != $input['password']) {
            $usersData['password'] = Hash::make($input['password']);
        }

        $res = User::where('id', $input['user_id'])->update($usersData);
        if ($res == 1) {
            DB::table('model_has_roles')->where('model_id', $input['user_id'])->delete();
            $user = User::find($input['user_id']);
            $user->assignRole($request->input('user_type_id'));

            return response()->json(['data' => 'success', 'msg' => 'User Updated Successfully!']);
        } elseif ($res == 0) {
            return response()->json(['data' => 'success', 'msg' => 'No Changes To Update!']);
        } else {
            return response()->json(['data' => 'error', 'msg' => $validator->errors()->all()]);
        }
    }

    public function addproduct(Request $request){

        $request->validate([
            'client_id' => 'required',
            'lob_id' => 'required',
            'product_name' => 'required',
        ]);

        $input = $request->all();

        $productData = [
            'client_id' => $input['client_id'],
            'lob_id' => $input['lob_id'],
            'product_name' =>$input['product_name'],
            'comments' => $input['comments'],
            'is_active' => $input['is_active'],
            'created_by' => Auth::id(),
            'created_at' => now(),
        ];



        $productId = Product::insertGetId($productData);

        if (isset($productId)) {
            $product = Product::find($productId);
        // return response()->json($product);
            return response()->json(['data' => 'success', 'msg' => 'Product Added Successfully!']);
        } else {
            return response()->json(['data' => 'error', 'msg' => $validator->errors()->all()]);
        }


    }

    public function edit_product(Request $request)
    {
        $id = $request->id;
        $productDetail = Product::where('id', $id)->first();

        return response()->json($productDetail);
    }

    public function productStatus($productid)
    {
        $product = Product::find($productid);
        
        if ($product->is_active == 1) {
            Product::where('id', $productid)->update(['is_active' => 0]);
        } else {
            Product::where('id', $productid)->update(['is_active' => 1]);
        }

        return redirect()->back()->with('success', 'Status Changed Successfully!');
    }

    public function update_product(Request $request)
    {
        $request->validate([
            'client_id_ed' => 'required',
            'lob_id_ed' => 'required',
            'product_name_ed' => 'required',
        ]);

        $input = $request->all();
        $isactive = isset($input['is_active_ed']) ? 1 : 0;

        $productData = [
            'client_id' => $input['client_id_ed'],
            'lob_id' => $input['lob_id_ed'],
            'product_name' =>$input['product_name_ed'],
            'comments' => $input['comments_ed'],
            'is_active' => $isactive,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ];

        $res = Product::where('id', $input['id_ed'])->update($productData);

        if ($res > 0) {
            return response()->json(['data' => 'success', 'msg' => 'Product Updated Successfully!']);
        } elseif ($res === 0) {
            return response()->json(['data' => 'success', 'msg' => 'No Changes To Update!']);
        } else {
            return response()->json(['data' => 'error', 'msg' => 'Failed to update service.']);
        }
    }



    public function mappingData(Request $request){
        $user = User::find($request->id);
        $serviceIds = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
        $assignedServiceIds = DB::table('oms_user_service_mapping')->where('user_id', $user->id)->where('is_active', 1)->pluck('service_id')->toArray();
        $unassignedServiceIds = array_diff($serviceIds, $assignedServiceIds);

        $unassignedService = DB::table('stl_item_description')
                ->whereIn('stl_item_description.id', $unassignedServiceIds)
                ->join('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
                ->select(
                    'stl_lob.id as lob_id',
                    'stl_lob.name as lob_name',
                    'stl_item_description.id',
                    'stl_item_description.project_code',
                    'stl_item_description.process_name'
                )
                ->orderBy('stl_lob.name')
                ->get();

            $groupedServices = $unassignedService->groupBy('lob_name');

            $groupedunassignedServiceServicesArray = $groupedServices->map(function ($items, $lob_name) {
                return [
                    'lob_name' => $lob_name,
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'project_code' => $item->project_code,
                            'process_name' => $item->process_name,
                        ];
                    })->values(),
                ];
            })->values();


        $assignedService = DB::table('stl_item_description')
            ->whereIn('stl_item_description.id', $assignedServiceIds)
            ->join('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
            ->select(
                'stl_lob.id as lob_id',
                'stl_lob.name as lob_name',
                'stl_item_description.id',
                'stl_item_description.project_code',
                'stl_item_description.process_name'
            )
            ->orderBy('stl_lob.name')
            ->get();



        $groupedAssignedServices = $assignedService->groupBy('lob_name');

        $groupedAssignedServicesArray = $groupedAssignedServices->map(function ($items, $lob_name) {
            return [
                'lob_name' => $lob_name,
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'project_code' => $item->project_code,
                        'process_name' => $item->process_name,
                    ];
                })->values(),
            ];
        })->values();

        $allService = DB::table('stl_item_description')
                ->join('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
                ->select(
                    'stl_lob.name as lob_name',
                    'stl_item_description.id',
                    'stl_item_description.project_code',
                    'stl_item_description.process_name'
                )
                ->orderBy('stl_lob.name')
                ->get();

            // Group the services by lob_name
            $groupedAllServices = $allService->groupBy('lob_name');

            // Transform the data into the desired format
            $groupedAllServicesArray = $groupedAllServices->map(function ($items, $lob_name) {
                return [
                    'lob_name' => $lob_name,
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'project_code' => $item->project_code,
                            'process_name' => $item->process_name,
                        ];
                    })->values(),
                ];
            })->values();

        $response = [
            'unassignedService' => $unassignedServiceIds ?? [],
            'assignedService' => $assignedServiceIds ?? [],
            'assignedServiceData' => $groupedAssignedServicesArray ?? [],
            'allService' => $groupedAllServicesArray ?? [],
        ];

        return response()->json($response);
    }

    public function getPreviouslyAssignedIDs(Request $request) {
        $userID = $request->userID;

        $assignedServices = ServiceUserMapping::where('user_id', $userID)
            ->where('is_active', 1)
            ->pluck('service_id');

        return response()->json(['assignedServiceIDs' => $assignedServices]);
    }


    public function updateMapping(Request $request) {
        $addIDs = (array) $request->add;
        $removeIDs = (array) $request->remove;
        $reactivateIDs = (array) $request->reactivate;
            $userID = $request->userID;

        if (!empty($userID)) {
            // Add services
            if (is_array($addIDs) && count($addIDs) > 0) {
                foreach ($addIDs as $serviceID) {
                    ServiceUserMapping::updateOrInsert(
                        ['service_id' => $serviceID, 'user_id' => $userID],
                        ['is_active' => 1]
                    );
                }
            }

            // Remove services
            if (is_array($removeIDs) && count($removeIDs) > 0) {
                foreach ($removeIDs as $serviceID) {
                    ServiceUserMapping::where('service_id', $serviceID)
                        ->where('user_id', $userID)
                        ->update(['is_active' => 0]);
                }
            }

            // Reactivate services (if necessary)
            if (is_array($reactivateIDs) && count($reactivateIDs) > 0) {
                foreach ($reactivateIDs as $serviceID) {
                ServiceUserMapping::updateOrInsert(
                        ['service_id' => $serviceID, 'user_id' => $userID],
                    ['is_active' => 1]
                );
            }
            }

            return response()->json(['data' => 'success']);
        }

        return response()->json(['data' => 'error']);
    }



    public function getUserList(Request $request)
    { 
        $ReportingList = [];
        if($request->reviewer_type == 'getAVps') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [2])->where('is_active', 1)->get();
        } elseif($request->reviewer_type == 'getBussinessHeads') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [3])->where('is_active', 1)->get();
        } elseif($request->reviewer_type == 'getPM_TL') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [5])->where('is_active', 1)->get();
        }elseif($request->reviewer_type == 'getSOPC') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [9])->where('is_active', 1)->get();
        }elseif($request->reviewer_type == 'getAdmin') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [23])->where('is_active', 1)->get();
        }elseif($request->reviewer_type == 'getVps') {
            $ReportingList = User::select('id', 'username', 'emp_id', 'user_type_id')->whereIn('user_type_id', [24])->where('is_active', 1)->get();
        }
        $html = '<option disabled selected value="">Select Reporting to</option>';
        if (!empty($ReportingList)) {
            foreach ($ReportingList as $Reporting) {
                $username = ucwords(trim($Reporting->emp_id.' ('.$Reporting->username.')'));
                $html .= '<option value="'.$Reporting->id.'">'.$username.'</option>';
            }
            return $html;
        } else {
            $html = '<option value="" disabled>Users not found</option>';
            return $html;
        }

        return '';
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Check the filename to determine which import logic to use
            $filename = $file->getClientOriginalName();
            if (strpos($filename, 'UserServiceMapping') !== false) {
                Excel::import(new UserServiceMappingImport, $file);
            } else {
                Excel::import(new AssignImport, $file);
            }

            return redirect()->back()->with('success', 'Data imported successfully!');
        } else {
            return response()->json(['error' => 'File upload failed'], 422);
        }
    }

    public function export(Request $request)
    {
        $exportData = ServiceUserMapping::with(['users:id,username,emp_id', 'projects:id,project_code'])->get();

        if ($exportData->isEmpty()) {
            return redirect()->back()->with('error', 'No data available for export');
        }

        return Excel::download(new UserServiceMappingExport($exportData), 'exportdata.xlsx');
    }

    public function importUser(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');

        if ($file && file_exists($file) && is_readable($file)) {
            $filename = 'exceldata_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->storeAs('Uploaded_Excel_Files', $filename);

            $original_file_name =  $file->getClientOriginalName();
            Excel::import(new AssignImport, $original_file_name);

            return redirect()->back()->with(['success' => 'Order Inserted Successfully!']);
        } else {
            return redirect()->back()->with(['error' => 'The file does not exist or is not readable']);
        }
    }

    public function getlobId(Request $request)
    {
        $filteredLob = DB::table('stl_lob')->select('id', 'client_id', 'name')->get();
        $clientIdToMatch = $request->client_id;
        $lobs = $filteredLob->filter(function($item) use ($clientIdToMatch) {
            $clientIds = json_decode($item->client_id, true);
            return in_array($clientIdToMatch, $clientIds);
        });

        return response()->json($lobs);
    }

    public function getprocessId(Request $request)
    {
        $process = DB::table('stl_process')
                    ->select('id', 'name')
                    ->where('lob_id', $request->lob_id)
                    ->orderBy('name', 'asc')
                    ->get();
       
        return response()->json($process);
    }

    public function sduploadfileImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
            'client_id' => 'required',
            'lob_id' => 'required',
            'process_id' => 'required',
        ]);

        $file = $request->file('file');

        if ($file && $file->getClientOriginalExtension() == 'xlsx' && $file->isValid()) {
            $filename = 'excelupload_' . uniqid() . '.' . $file->getClientOriginalExtension();
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
            } elseif (Auth::user()->hasRole('VP') && $totalRowCount >= 4000) {
                $splitSize = ($totalRowCount/4);
            } elseif (Auth::user()->hasRole('Business Head') && $totalRowCount >= 3000) {
                $splitSize = ($totalRowCount/3);
            } elseif (Auth::user()->hasRole('PM/TL') && $totalRowCount > 2000) {
                $splitSize = ($totalRowCount/2);
            }elseif (Auth::user()->hasRole('Admin') && $totalRowCount >= 4000) {
                $splitSize = ($totalRowCount/4);
            }elseif (Auth::user()->hasRole('AVP') && $totalRowCount >= 4000) {
                $splitSize = ($totalRowCount/4);
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
            $auditId = CountyInstructionAudit::insertGetId([
                'file_name' => $original_file_name,
                'total_rows' => $totalRowCount,
                'created_at' => now(),
                'created_by' => Auth::id()
            ]);

            if (file_exists(storage_path('app/Uploaded_Excel_Files/' . $filename))) {
                unlink(storage_path('app/Uploaded_Excel_Files/' . $filename));
            }

            foreach (glob($outputFilesPath) as $file) {
                Excel::import(new SduploadImport(Auth::id(), $auditId, $request->client_id, $request->lob_id, $request->process_id), $file);
            }

            return response()->json(['success' => 'Excel Uploaded Successfully!', 'bacthId' => $auditId]);
        } else {
            return response()->json(['error' => 'The file does not exist, is not readable, or is not an XLSX file']);
        }
    }

    public function exportCIFailedOrders($audit_id)
    {
        $failedOrders = CountyInstructionTemp::where('audit_id', $audit_id)->get();

        $export = new FailedCIOrdersExport($failedOrders);
        $exportFileName = 'failed_CI_orders_export_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $exportFileName);
    }

    public function getLobAndProcess(Request $request)
    {
        $clientId = $request->input('client_id');

        $lobs = DB::table('stl_item_description')
        ->leftjoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->where('stl_item_description.is_approved', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.client_id', $clientId)
        // ->whereIn('stl_item_description.id', $mapped_lobs)
        ->select(
            'stl_lob.name as name',
            'stl_lob.client_id as client_id',
            'stl_lob.id as id',
        )
        ->distinct()
        ->get();

        // Iterate through the LOBs and match them with processes
        foreach ($lobs as $lob) {
            $lob->processes = DB::table('stl_process')
                ->where('lob_id', $lob->id) // Match LOB ID with the process table
                ->where('is_active', 1) // Only include active processes
                ->orderBy('name', 'asc')
                ->get();
        }
    
        return response()->json($lobs);
    }

}
