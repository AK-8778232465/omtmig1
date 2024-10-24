<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Hash;
use Session;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\City;
use App\Models\Client;
use App\Models\Process;
use App\Models\County;
use App\Models\OrderCreation;
use App\Models\State;
use App\Models\Status;
use App\Models\stl_item_description;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use DataTables;
use Illuminate\Support\Collection;


class HomeController extends Controller
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

    //  public function index(Request $request)
    //  {
    //     return view('app.orders.comingsoon');
    //  }


     public function index(Request $request)
     {
        $user = User::where('id', Auth::id())->first();
        $processList=[];
        if ($user->is_active == 1) {
            session(['uid' => Auth::id()]);
            session(['user_type_id' => $user->user_type_id]);
            session(['company_id' => isset($user->company_id) ? $user->company_id : 0]);
            $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
            if (Auth::user()->hasRole('Super Admin')) {
                $processIds = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
            } else {
                $processIds = DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
     }
            $processList = DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->whereIn('id', $processIds)->select('id', 'process_name', 'project_code')->get();

            $clients = Client::select('id','client_no', 'client_name')->where('is_active', 1)->where('is_approved', 1)->get();


        } else {
            Auth::logout();

            return redirect('/');
        }

        return view('app.dashboard.index', compact('processList','clients'));
    }


    public function profileupdate(Request $request)
    {


        $user = Auth::user();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json(['success' => 'Password updated successfully.']);
    }

    public function profileEdit(Request $request)
    {
        return view('app.dashboard.profile_edit');
    }

    public function getCounty(Request $request)
    {
        $getCounty['county'] = County::select('id', 'stateId', 'county_name')->where('stateId', $request->state_id)->get();

        return response()->json($getCounty);
    }

    public function getCities(Request $request)
    {
        $county_id = $request->county_id;
        $county = County::with('cities')->find($county_id);
        $cities = $county->cities()->select('id', 'city')->get();
        return response()->json(['cities' => $cities]);
    }



    public function dashboard_dropdown(Request $request)
    {
        $client_id = null;

        $getclient_id = $request->client_id;

        if (!is_array($getclient_id)) {
            $getclient_id = [$getclient_id];
        }

        if (in_array('All', $getclient_id)) {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->get();
        } else {
            $getProject = DB::table('stl_item_description')
                            ->select('id', 'client_id', 'process_name', 'project_code')
                            ->whereIn('client_id', $getclient_id)
                            ->get();
        }

        return response()->json($getProject);
    }


    public function dashboard_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        // Define default request data values
        // $from_date = $request->input('from_date');
        // $to_date = $request->input('to_date');
        $project_id = $request->input('project_id', ['All']); // Default to 'All'
        $client_id = $request->input('client_id', ['All']); // Default to 'All'
        $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
   
        $from_date = null;
        $to_date = null;
   
        if ($fromDateRange && $toDateRange) {
            $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $to_date = $from_date;
            }
        }

        if (!is_array($project_id)) {
            $project_id = explode(',', $project_id); 
        }

        if (!is_array($client_id)) {
            $client_id = explode(',', $client_id); 
        }

        $statusCountsQuery = OrderCreation::query()->with('process', 'client')
        ->whereHas('process', function ($query) {
            $query->where('stl_item_description.is_approved', 1);
        })
        ->whereHas('client', function ($query) {
            $query->where('stl_client.is_approved', 1);
        });
        $statusCountsQuery3 = clone $statusCountsQuery;

        // Handle project_id and client_id cases
        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $statusCountsQuery->with('process', 'client')
                ->whereIn('process_id', $processIds)
                ->where('status_id', '!=', 5)
                ->where('is_active', 1)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $statusCountsQuery->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('status_id', '!=', 5)
                ->where('is_active', 1)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date);
            } else {
                // Case: project_id is 'All'
                $statusCountsQuery->whereIn('process_id', $processIds)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->where('status_id', '!=', 5)
                    ->where('is_active', 1);
            }
        }

        // Handle different user types
        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif ($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id)
                ->whereNotIn('status_id', [1, 13]);
            } elseif ($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user){
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            }
        }

        $statusCountsQuery1 = $statusCountsQuery2 = $statusCountsQuery;
        //  return response()->json($statusCountsQuery3->get());

        $statusCounts = $statusCountsQuery->groupBy('status_id')
                    ->selectRaw('count(*) as count, status_id')
                    ->where('is_active', 1)
                    ->pluck('count', 'status_id');
        $yetToAssignUser = 0;
        $yetToAssignQa = 0;

        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            // Handle additional query based on project_id and client_id
            if (in_array('All', $project_id) && !in_array('All', $client_id)) {
                // Case: Project_id is 'All' and client_id is not 'All'
                $yetToAssignUser = $statusCountsQuery1->where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->count();
                $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                    ->whereIn('process_id', $processIds)
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->count();
            } elseif (!in_array('All', $project_id) && in_array('All', $client_id)) {
                // Case: project_id is specified and client_id is not 'All'
                $yetToAssignUser = $statusCountsQuery1->where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->count();

                $yetToAssignQa = $statusCountsQuery2->where('assignee_qa_id', null)
                    ->where('status_id', 4)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->count();
                $StatusCompletedCount = $statusCountsQuery3
                    ->whereIn('process_id', $project_id)
                    ->whereIn('process_id', $processIds)
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->count();
            }elseif(!in_array('All', $project_id) && !in_array('All', $client_id)){
                $yetToAssignUser = $statusCountsQuery1->with('process', 'client')
                    ->where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $project_id)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->count();

                $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                    ->whereIn('process_id', $processIds)
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->whereIn('process_id', $project_id)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->count();
            } else {
                // Case: project_id is 'All' and client_id is 'All'
                $yetToAssignUser = $statusCountsQuery1->where('assignee_user_id', null)
                    ->where('status_id', 1)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $processIds)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->count();

                $yetToAssignQa = $statusCountsQuery2->where('assignee_qa_id', null)
                    ->where('status_id', 4)
                    ->where('is_active', 1)
                    ->whereIn('process_id', $processIds)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->count();

                $StatusCompletedCount = $statusCountsQuery3
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date)
                    ->whereIn('process_id', $processIds)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->count();
            }

            $statusCounts[1] = (!empty($statusCounts[1]) ? $statusCounts[1] : 0) - $yetToAssignUser;
            $statusCounts[4] = (!empty($statusCounts[4]) ? $statusCounts[4] : 0);
            $statusCounts[5] = $StatusCompletedCount;
            $statusCounts[6] = $yetToAssignUser;
        } else {
            if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                if(in_array('All', $project_id) && !in_array('All', $client_id)){
                    if ($user->user_type_id == 6) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();
                    } elseif ($user->user_type_id == 7) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();
                    } elseif ($user->user_type_id == 8) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                            })
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();

                    }

                } elseif(!in_array('All', $project_id) && in_array('All', $client_id)){
                    if ($user->user_type_id == 6) {
                        $StatusCompletedCount = $statusCountsQuery3
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();
                    } elseif ($user->user_type_id == 7) {
                        $StatusCompletedCount = $statusCountsQuery3
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();
                    } elseif ($user->user_type_id == 8) {
                        $StatusCompletedCount = $statusCountsQuery3
                            ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                                     })
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();

                    }

                } elseif(!in_array('All', $project_id) && !in_array('All', $client_id)){
                    if ($user->user_type_id == 6) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $project_id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->where('assignee_user_id', $user->id)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();
                    } elseif ($user->user_type_id == 7) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $project_id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->where('assignee_qa_id', $user->id)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();
                    } elseif ($user->user_type_id == 8) {
                        $StatusCompletedCount = $statusCountsQuery3->with('process', 'client')
                            ->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                            })
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $project_id)
                            ->whereIn('process_id', $processIds)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->count();

                    }

                }else {

                    if ($user->user_type_id == 6) {
                        $StatusCompletedCount = $statusCountsQuery3
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();
                    } elseif ($user->user_type_id == 7) {
                        $StatusCompletedCount = $statusCountsQuery3
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();
                    } elseif ($user->user_type_id == 8) {
                        $StatusCompletedCount = $statusCountsQuery3->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                  ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->whereDate('order_date', '>=', $from_date)
                            ->whereDate('order_date', '<=', $to_date)
                            ->whereIn('process_id', $processIds)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->count();

                    }

                }
               
            }
            $statusCounts[5] = $StatusCompletedCount;
            $statusCounts[6] = [0];
        }

        return response()->json([
            'StatusCounts' => $statusCounts,
        ]);
    }

    public function previous_count(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
        // $from_date = $request->input('from_date');
        // $to_date = $request->input('to_date');
        $project_id = $request->input('project_id', ['All']); // Default to 'All'
        $client_id = $request->input('client_id', ['All']); // Default to 'All'

        $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
   
        $from_date = null;
        $to_date = null;
   
        if ($fromDateRange && $toDateRange) {
            $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $to_date = $from_date;
            }
        }

        // Ensure project_id and client_id are arrays
        if (!is_array($project_id)) {
            $project_id = explode(',', $project_id); // Convert string to array
        }

        if (!is_array($client_id)) {
            $client_id = explode(',', $client_id); // Convert string to array
        }

        $statusCountsQuery = OrderCreation::query()->with('process', 'client')
        ->whereHas('process', function ($query) {
            $query->where('stl_item_description.is_approved', 1);
        })
        ->whereHas('client', function ($query) {
            $query->where('stl_client.is_approved', 1);
        });
        $statusCountsQuery2 = clone $statusCountsQuery;

        // Handle project_id and client_id cases
        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $statusCountsQuery->with('process', 'client')
                ->whereIn('process_id', $processIds)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('status_id', '!=', 3)
                ->where('completion_date', null)
                ->whereDate('order_date', '<', $from_date)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $statusCountsQuery->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('status_id', '!=', 3)
                ->where('completion_date', null)
                ->whereDate('order_date', '<', $from_date);
            } else {
                // Case: project_id is 'All'
                $statusCountsQuery->whereIn('process_id', $processIds)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('status_id', '!=', 3)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '<', $from_date);
            }
        }

        // Handle different user types
        if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if ($user->user_type_id == 6) {
                $statusCountsQuery->where('assignee_user_id', $user->id);
            } elseif ($user->user_type_id == 7) {
                $statusCountsQuery->where('assignee_qa_id', $user->id)
                ->whereNotIn('status_id', [1, 13]);
            } elseif ($user->user_type_id == 8) {
                $statusCountsQuery->where(function ($query) use($user){
                    $query->where('assignee_user_id', $user->id)
                        ->orWhere('assignee_qa_id', $user->id);
                });
            }
        }

        // Clone the query for statusCountsQuery1 and statusCountsQuery2
        $statusCountsQuery1 = clone $statusCountsQuery;

        // Get the count of group by status_id for statusCountsQuery1
        $carriedCount = $statusCountsQuery1
            ->selectRaw('status_id, count(*) as total_orders')
            ->groupBy('status_id')
            ->get();

        $statusCounts['carriedCount'] = $carriedCount;
        if (in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
            if(in_array('All', $project_id) && !in_array('All', $client_id)){
                // Filter for getorderId query
                $getorderId = $statusCountsQuery2->with('process', 'client')
                    ->select('id', 'order_date', 'completion_date', 'status_id')
                    ->whereIn('process_id', $processIds)
                    ->whereDate('order_date', '<', $from_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->get();

                // Filter for carriedOverCompletedCount2 query
                $carriedOverCompletedCount2 = $statusCountsQuery2->with('process', 'client')
                    ->select('id', 'completion_date', 'status_id')
                    ->whereIn('process_id', $processIds)
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->whereHas('process', function ($query) use ($client_id) {
                        $query->whereIn('client_id', $client_id);
                    })
                    ->get();

            }else{
                if(!in_array('All', $project_id)){
                    // Filter for getorderId query
                    $getorderId = $statusCountsQuery2->select('id', 'order_date', 'completion_date', 'status_id')
                    ->whereIn('process_id', $processIds)
                    ->whereIn('process_id', $project_id)
                    ->whereDate('order_date', '<', $from_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->get();

                // Filter for carriedOverCompletedCount2 query
                $carriedOverCompletedCount2 = $statusCountsQuery2
                    ->select('id', 'completion_date', 'status_id')
                    ->whereDate('completion_date', '>=', $from_date)
                    ->whereDate('completion_date', '<=', $to_date)
                    ->where('status_id', 5)
                    ->where('is_active', 1)
                    ->get();
                }else{
                    // Filter for getorderId query
                    $getorderId = $statusCountsQuery2
                        ->select('id', 'order_date', 'completion_date', 'status_id')
                        ->whereDate('order_date', '<', $from_date)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->get();

                    // Filter for carriedOverCompletedCount2 query
                    $carriedOverCompletedCount2 = $statusCountsQuery2
                        ->select('id', 'completion_date', 'status_id')
                        ->whereDate('completion_date', '>=', $from_date)
                        ->whereDate('completion_date', '<=', $to_date)
                        ->where('status_id', 5)
                        ->where('is_active', 1)
                        ->get();
                }
            }
        } else {
            if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
                if ($user->user_type_id == 6) {
                    if(in_array('All', $project_id) && !in_array('All', $client_id)){
                        // Filter for getorderId query
                        $getorderId = $statusCountsQuery2->with('process', 'client')
                            ->select('id', 'order_date', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();
        
                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2->with('process', 'client')
                            ->select('id', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->where('assignee_user_id', $user->id)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();
        
                    }else{
                        if(!in_array('All', $project_id)){
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2->select('id', 'order_date', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereIn('process_id', $project_id)
                            ->where('assignee_user_id', $user->id)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();
        
                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2
                            ->select('id', 'completion_date', 'status_id')
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('assignee_user_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();
                        }else{
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2
                                ->select('id', 'order_date', 'completion_date', 'status_id')
                                ->whereDate('order_date', '<', $from_date)
                                ->where('assignee_user_id', $user->id)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();
        
                            // Filter for carriedOverCompletedCount2 query
                            $carriedOverCompletedCount2 = $statusCountsQuery2
                                ->select('id', 'completion_date', 'status_id')
                                ->whereDate('completion_date', '>=', $from_date)
                                ->whereDate('completion_date', '<=', $to_date)
                                ->where('assignee_user_id', $user->id)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();
                        }
                    }
                } elseif($user->user_type_id == 7){
                    if(in_array('All', $project_id) && !in_array('All', $client_id)){
                        // Filter for getorderId query
                        $getorderId = $statusCountsQuery2->with('process', 'client')
                            ->select('id', 'order_date', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();
        
                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2->with('process', 'client')
                            ->select('id', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();
        
                    }else{
                        if(!in_array('All', $project_id)){
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2->select('id', 'order_date', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereIn('process_id', $project_id)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();
        
                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2
                            ->select('id', 'completion_date', 'status_id')
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('assignee_qa_id', $user->id)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();
                        }else{
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2
                                ->select('id', 'order_date', 'completion_date', 'status_id')
                                ->whereDate('order_date', '<', $from_date)
                                ->where('assignee_qa_id', $user->id)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();
        
                            // Filter for carriedOverCompletedCount2 query
                            $carriedOverCompletedCount2 = $statusCountsQuery2
                                ->select('id', 'completion_date', 'status_id')
                                ->whereDate('completion_date', '>=', $from_date)
                                ->whereDate('completion_date', '<=', $to_date)
                                ->where('assignee_qa_id', $user->id)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();
                        }
                    }
                }elseif($user->user_type_id == 8){
                    if(in_array('All', $project_id) && !in_array('All', $client_id)){
                        // Filter for getorderId query
                        $getorderId = $statusCountsQuery2->with('process', 'client')->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->select('id', 'order_date', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();


        
                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2->with('process', 'client')->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->select('id', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->whereHas('process', function ($query) use ($client_id) {
                                $query->whereIn('client_id', $client_id);
                            })
                            ->get();


                    }else{
                        if(!in_array('All', $project_id)){
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2->select('id', 'order_date', 'completion_date', 'status_id')->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                            })
                            ->whereIn('process_id', $processIds)
                            ->whereIn('process_id', $project_id)
                            ->whereDate('order_date', '<', $from_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();

                        // Filter for carriedOverCompletedCount2 query
                        $carriedOverCompletedCount2 = $statusCountsQuery2->where(function ($query) use($user){
                            $query->where('assignee_user_id', $user->id)
                                ->orWhere('assignee_qa_id', $user->id);
                        })
                            ->select('id', 'completion_date', 'status_id')
                            ->whereIn('process_id', $processIds)
                            ->whereDate('completion_date', '>=', $from_date)
                            ->whereDate('completion_date', '<=', $to_date)
                            ->where('status_id', 5)
                            ->where('is_active', 1)
                            ->get();

                        }else{
                            // Filter for getorderId query
                            $getorderId = $statusCountsQuery2->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                    ->orWhere('assignee_qa_id', $user->id);
                            })
                                ->select('id', 'order_date', 'completion_date', 'status_id')
                                ->whereIn('process_id', $processIds)
                                ->whereDate('order_date', '<', $from_date)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();

                            // Filter for carriedOverCompletedCount2 query
                            $carriedOverCompletedCount2 = $statusCountsQuery2->where(function ($query) use($user){
                                $query->where('assignee_user_id', $user->id)
                                    ->orWhere('assignee_qa_id', $user->id);
                            })
                                ->select('id', 'completion_date', 'status_id')
                                ->whereIn('process_id', $processIds)
                                ->whereDate('completion_date', '>=', $from_date)
                                ->whereDate('completion_date', '<=', $to_date)
                                ->where('status_id', 5)
                                ->where('is_active', 1)
                                ->get();
                        }
                    }
                }
            }
        }

        // Convert query results to collections
        $getorderId = collect($getorderId);
        $carriedOverCompletedCount2 = collect($carriedOverCompletedCount2);

        // Extract the IDs from both collections
        $getorderIdIds = $getorderId->pluck('id');
        $carriedOverCompletedCount2Ids = $carriedOverCompletedCount2->pluck('id');

        // Find common IDs
        $commonIds = $getorderIdIds->intersect($carriedOverCompletedCount2Ids);

        // Filter the original collections to only include the common IDs
        $carriedOverCompletedIds = $getorderId->whereIn('id', $commonIds);

        // Group the result by status_id and count the occurrences of each status_id
        $carriedOverCompletedCount = $carriedOverCompletedIds->groupBy('status_id')->map->count();

        // Store the result in statusCounts array
        $statusCounts['carriedOverCompletedCount'] = $carriedOverCompletedCount;

    return response()->json(['StatusCounts' => $statusCounts]);
}

    public function dashboard_clientwise_count(Request $request)
{
    $user = Auth::user();

    // $fromDate = $request->input('from_date');
    // $toDate = $request->input('to_date');
    $client_id = $request->input('client_id');
    $project_id = $request->input('project_id');

    $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
   
        $fromDate = null;
        $toDate = null;
   
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $statusCountsQuery = OrderCreation::query()
        ->select('stl_client.client_name', 'stl_item_description.process_name', 'oms_order_creations.process_id', 'stl_item_description.project_code')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->where('oms_order_creations.is_active',1)
        ->whereIn('oms_order_creations.process_id', $processIds)
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 1 AND oms_order_creations.assignee_user_id IS NOT NULL THEN 1 END) AS WIP')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 2 THEN 2 END) AS Hold')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 3 THEN 3 END) AS Cancelled')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 4 THEN 4 END) AS Send_for_QC')
         ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 5 AND DATE_FORMAT(oms_order_creations.completion_date, "%Y-%m-%d") >= ? AND DATE_FORMAT(oms_order_creations.completion_date, "%Y-%m-%d") <= ? THEN 1 ELSE NULL END) AS Completed', [$fromDate, $toDate])

        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 13 THEN 13 END) AS Coversheet_Prep')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 14 THEN 14 END) AS Clarification')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 15 THEN 15 END) AS Doc_Purchase')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 18 THEN 18 END) AS Ground_Abstractor')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 16 THEN 16 END) AS Typing')
        ->selectRaw('COUNT(CASE WHEN oms_order_creations.status_id = 17 THEN 17 END) AS Typing_QC')
        ->groupBy('stl_client.client_name', 'stl_item_description.process_name', 'oms_order_creations.process_id', 'stl_item_description.project_code');

    if ($user->user_type_id == 6) {
        $statusCountsQuery->where('oms_order_creations.assignee_user_id', $user->id);
    }

    if ($user->user_type_id == 7) {
        $statusCountsQuery->where('oms_order_creations.assignee_qa_id', $user->id)
             ->whereNotIn('status_id', [1, 13]);
    }
    if ($user->user_type_id == 8) {
        $statusCountsQuery->where(function($query) use ($user) {
            $query->where('oms_order_creations.assignee_qa_id', $user->id)
                  ->orWhere('oms_order_creations.assignee_user_id', $user->id);
        });
    }    

    // Apply client_id condition
    if (!empty($client_id) && $client_id[0] !== 'All') {
        $statusCountsQuery->whereIn('stl_client.id', $client_id);
    }

    // Apply project_id condition
    if (!empty($project_id) && $project_id[0] !== 'All') {
        $statusCountsQuery->whereIn('oms_order_creations.process_id', $project_id);
    }

    // Apply date filtering
if ($fromDate && $toDate) {
    $statusCountsQuery->where(function($datequery) use ($fromDate, $toDate) {
        $datequery->whereDate('oms_order_creations.order_date', '>=', $fromDate)
                  ->whereDate('oms_order_creations.order_date', '<=', $toDate);
    });
}


    $dataForDataTables = $statusCountsQuery->get();

    $output = [];
    foreach ($dataForDataTables as $data) {
        // Initialize sum variable
        $sum = 0;
        // Add counts to sum conditionally
        if (isset($data->WIP)) $sum += $data->WIP;
        if (isset($data->Hold)) $sum += $data->Hold;
        if (isset($data->Cancelled)) $sum += $data->Cancelled;
        if (isset($data->Send_for_QC)) $sum += $data->Send_for_QC;
        if (isset($data->Completed)) $sum += $data->Completed;
        if (isset($data->Coversheet_Prep)) $sum += $data->Coversheet_Prep;
        if (isset($data->Clarification)) $sum += $data->Clarification;
        if (isset($data->Doc_Purchase)) $sum += $data->Doc_Purchase;
        if (isset($data->Typing)) $sum += $data->Typing;
        if (isset($data->Typing_QC)) $sum += $data->Typing_QC;
        if (isset($data->Ground_Abstractor)) $sum += $data->Ground_Abstractor;


        $output[] = [
            'client_name' => $data->client_name,
            'process_name' => $data->process_name,
            'project_code' => $data->project_code,
            'WIP' => $data->WIP,
            'Hold' => $data->Hold,
            'Cancelled' => $data->Cancelled,
            'Send for QC' => $data->Send_for_QC,
            'Completed' => $data->Completed,
            'Coversheet Prep' => $data->Coversheet_Prep,
            'Clarification' => $data->Clarification,
            'Doc Purchase' => $data->Doc_Purchase,
            'Typing' => $data->Typing,
            'Typing QC' => $data->Typing_QC,
            'Ground Abstractor' => $data->Ground_Abstractor,
            'All' => $data->WIP + $data->Hold + $data->Cancelled +  $data->Send_for_QC + $data->Completed +  $data->Coversheet_Prep + $data->Clarification + $data->Doc_Purchase + $data->Typing + $data->Typing_QC + $data->Ground_Abstractor, // Add the sum as 'All'
            // Add other fields as needed
        ];
    }

    return response()->json(['data' => $output]);
}

public function dashboard_userwise_count(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    // $fromDate = $request->input('from_date');
    // $toDate = $request->input('to_date');
    $client_id = $request->input('client_id');
    $project_id = $request->input('project_id');

    $selectedDateFilter = $request->input('selectedDateFilter');

    $fromDateRange = $request->input('from_date');
    $toDateRange = $request->input('to_date');

    $fromDate = null;
    $toDate = null;

    if ($fromDateRange && $toDateRange) {
        $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/';
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $toDate = $fromDate;
        }
    }

    $statusCountsQuery = OrderCreation::query();
    $statusCountsQuery
        ->whereNotNull('assignee_user_id')
        ->where(function($datequery) use ($fromDate, $toDate) {
            $datequery->whereDate('oms_order_creations.order_date', '>=', $fromDate)
                      ->whereDate('oms_order_creations.order_date', '<=', $toDate);
        })
        ->leftJoin('oms_users', 'oms_order_creations.assignee_user_id', '=', 'oms_users.id')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->selectRaw('
            CONCAT(oms_users.emp_id, " (", oms_users.username, ")") as userinfo,
            SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) as `status_1`,
            SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as `status_2`,
            SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as `status_3`,
            SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) as `status_4`,
        SUM(CASE WHEN status_id = 5 AND DATE_FORMAT(completion_date, "%Y-%m-%d") BETWEEN ? AND ? THEN 1 ELSE 0 END) as `status_5`,
            SUM(CASE WHEN status_id = 13 THEN 1 ELSE 0 END) as `status_13`,
            SUM(CASE WHEN status_id = 14 THEN 1 ELSE 0 END) as `status_14`,
            SUM(CASE WHEN status_id = 15 THEN 1 ELSE 0 END) as `status_15`,
            SUM(CASE WHEN status_id = 16 THEN 1 ELSE 0 END) as `status_16`,
            SUM(CASE WHEN status_id = 17 THEN 1 ELSE 0 END) as `status_17`,
            SUM(CASE WHEN status_id = 18 THEN 1 ELSE 0 END) as `status_18`,

            COUNT(*) as `All`', [$fromDate, $toDate])
        ->where('oms_order_creations.is_active', 1)
        ->whereIn('oms_order_creations.process_id', $processIds)
        ->groupBy('oms_order_creations.assignee_user_id');

    if (!empty($project_id) && $project_id[0] !== 'All') {
        $statusCountsQuery->whereIn('oms_order_creations.process_id', $project_id);
    }

    if (!empty($client_id) && $client_id[0] !== 'All') {
        $statusCountsQuery->whereIn('stl_item_description.client_id', $client_id);
    }

    $statusCounts = $statusCountsQuery->get();

    $dataForDataTables = $statusCounts->map(function ($count) {
        return [
            'userinfo' => $count->userinfo,
            'status_1' => $count->status_1,
            'status_2' => $count->status_2,
            'status_3' => $count->status_3,
            'status_4' => $count->status_4,
            'status_5' => $count->status_5,
            'status_13' => $count->status_13,
            'status_14' => $count->status_14,
            'status_15' => $count->status_15,
            'status_16' => $count->status_16,
            'status_17' => $count->status_17,
            'status_18' => $count->status_18,
            'All' => $count->status_1 + $count->status_2 + $count->status_3 + $count->status_4 + $count->status_5 + $count->status_13 + $count->status_14 + $count->status_15 + $count->status_16 + $count->status_17 + $count->status_18,
        ];
    });

    return Datatables::of($dataForDataTables)->toJson();
}

    private function getProcessIdsBasedOnUserRole($user)
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return DB::table('stl_item_description')->where('is_approved', 1)->where('is_active', 1)->pluck('id')->toArray();
        } else {
            $reportingUserIds = User::getAllLowerLevelUserIds(Auth::id());
            return DB::table('oms_user_service_mapping')->whereIn('user_id', $reportingUserIds)->where('is_active', 1)->pluck('service_id')->toArray();
        }
    }



    public function revenue_detail(Request $request)
    {
            $user = Auth::user();
            $processIds = $this->getProcessIdsBasedOnUserRole($user);

            // $fromDate = $request->input('from_date');
            // $toDate = $request->input('to_date');
            $client_ids = $request->input('client_id');


            $selectedDateFilter = $request->input('selectedDateFilter');

            $fromDateRange = $request->input('from_date');
            $toDateRange = $request->input('to_date');
        
            $fromDate = null;
            $toDate = null;
        
            if ($fromDateRange && $toDateRange) {
                $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
                $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
            } else {
                $datePattern = '/(\d{2}-\d{2}-\d{4})/';
                if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                    list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                    $fromDateText = trim($fromDateText);
                    $toDateText = trim($toDateText);
                    preg_match($datePattern, $fromDateText, $fromDateMatches);
                    preg_match($datePattern, $toDateText, $toDateMatches);
                    $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                    $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
                } else {
                    preg_match($datePattern, $selectedDateFilter, $dateMatches);
                    $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                    $toDate = $fromDate;
                }
            }

            $query = DB::table('stl_item_description')
                ->select(
                    'oms_order_creations.process_id',
                    'stl_item_description.project_code',
                    'stl_item_description.process_name',
                    'stl_client.client_name',
                    'stl_client.client_no',
                    DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                    DB::raw('COUNT(*) as num_orders_completed'),
                    DB::raw('SUM(stl_item_description.cost) as total_revenue')
                )
                ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
                ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
                ->where('stl_item_description.billing_type_id', 1)
                ->where('oms_order_creations.status_id', 5)
                ->where('oms_order_creations.is_active', 1)
                ->whereIn('oms_order_creations.process_id', $processIds)
                ->groupBy('stl_item_description.project_code')
                ->groupBy('stl_item_description.process_name')
                ->groupBy('oms_order_creations.process_id')
                ->groupBy('stl_client.client_name')
                ->groupBy('stl_client.client_no')
                ->groupBy('oms_order_creations.order_date')
                ->orderBy('stl_item_description.project_code');

            if ($fromDate && $toDate) {
                $query->where(function($datequery) use ($fromDate, $toDate) {
                    $datequery->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
                });
            }

            if (!empty($client_ids) && $client_ids[0] !== 'All') {
                $query->whereIn('stl_client.id', $client_ids);
            }

            $revenueDetails = $query->get();
            $grandTotalRevenue = $revenueDetails->sum('total_revenue');

            $output = [];

            foreach ($revenueDetails as $revenueDetail) {
                $processId = $revenueDetail->process_id;

                if (isset($output[$processId])) {
                    $output[$processId]['No of orders completed'] += $revenueDetail->num_orders_completed;
                    $output[$processId]['Total'] += $revenueDetail->total_revenue;
                } else {
                    $output[$processId] = [
                        'id' => $processId,
                        'Client Name' => $revenueDetail->client_name,
                        'Client Code' => $revenueDetail->client_no,
                        'Date' => $revenueDetail->order_date,
                        'Process Name' => str_replace('&amp;', '&', $revenueDetail->process_name),
                        'Project Code' => $revenueDetail->project_code,
                        'No of orders completed' => $revenueDetail->num_orders_completed,
                        'Total' => $revenueDetail->total_revenue,
                    ];
                }
            }

            foreach ($output as &$item) {
                $item['Total'] = number_format($item['Total'], 2, '.', '');
                $item['Unit cost'] = $item['No of orders completed'] > 0 ? $item['Total'] / $item['No of orders completed'] : 0;
                $item['Unit cost'] = number_format($item['Unit cost'], 2, '.', ''); // Formatting unit cost to 5 decimal places
            }

            unset($item);

            return Datatables::of($output)->toJson();
    }


    public function revenue_detail_client(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        // $fromDate = $request->input('fromDate');
        // $toDate = $request->input('toDate');
        $client_ids = $request->input('client_id');

        $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
    
        $fromDate = null;
        $toDate = null;
    
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

        $query = DB::table('stl_item_description')
            ->select(
                'stl_item_description.project_code',
                'stl_client.id',
                'stl_client.client_name',
                'stl_client.client_no',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(DISTINCT oms_order_creations.order_id) as total_orders_completed'), // Counting distinct order IDs
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('stl_item_description.project_code', 'stl_client.client_name', 'stl_client.client_no','stl_client.id');

        if ($fromDate && $toDate) {
                $query->where(function($datequery) use ($fromDate, $toDate) {
                    $datequery->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
                });
            }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }


        $revenueDetails = $query->get();
        $grandTotalRevenue = $revenueDetails->sum('total_revenue');

        $output = [];

        foreach ($revenueDetails as $revenueDetail) {
            $clientCode = $revenueDetail->client_no;


            if (isset($output[$clientCode])) {

                $output[$clientCode]['No of orders completed'] += $revenueDetail->total_orders_completed;
                $output[$clientCode]['Total'] += $revenueDetail->total_revenue;
            } else {

                $output[$clientCode] = [
                    'id' => $revenueDetail->id,
                    'Date' => $revenueDetail->order_date,
                    'Project Code' => $revenueDetail->project_code,
                    'Client Code' => $revenueDetail->client_no,
                    'Client Name' => $revenueDetail->client_name,
                    'No of orders completed' => $revenueDetail->total_orders_completed,
                    'Total' => $revenueDetail->total_revenue,
                ];
            }
        }

        foreach ($output as &$item) {
            $item['Total'] = number_format($item['Total'], 2, '.', '');
        }

        unset($item);

        return Datatables::of($output)->toJson();
    }





    public function order_detail(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        // $fromDate = $request->input('from_date');
        // $toDate = $request->input('to_date');
        $client_ids = $request->input('client_id');
        $process_id = $request->input('projectId');

        $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
    
        $fromDate = null;
        $toDate = null;
    
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

        $query = DB::table('stl_item_description')
            ->select(
                'oms_order_creations.process_id',
                'stl_item_description.project_code',
                'stl_item_description.process_name',
                'stl_client.client_name',
                'stl_client.client_no',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(*) as num_orders_completed'),
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->groupBy('stl_item_description.project_code')
            ->groupBy('stl_item_description.process_name')
            ->groupBy('oms_order_creations.process_id')
            ->groupBy('stl_client.client_name')
            ->groupBy('stl_client.client_no')
            ->groupBy('oms_order_creations.order_date')
            ->orderBy('stl_item_description.project_code');

        if ($fromDate && $toDate) {
                $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
        }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }

        if (!empty($process_id)) {
            $query->where('oms_order_creations.process_id', $process_id);
        } else {
            $query->whereIn('oms_order_creations.process_id', $processIds);
        }


        $revenueDetails = $query->get();
        $grandTotalRevenue = $revenueDetails->sum('total_revenue');

        $output = [];

        foreach ($revenueDetails as $revenueDetail) {

            $processName = str_replace('&amp;', '&', $revenueDetail->process_name);

            $unitCost = $revenueDetail->num_orders_completed > 0 ? $revenueDetail->total_revenue / $revenueDetail->num_orders_completed : 0;

            $output[] = [
                'id' => $revenueDetail->process_id,
                'Client Name' => $revenueDetail->client_name,
                'Client Code' => $revenueDetail->client_no,
                'Date' => $revenueDetail->order_date,
                'Process Name' =>  $processName ,
                'Project Code' => $revenueDetail->project_code,
                'No of orders completed' => $revenueDetail->num_orders_completed,
                'Unit cost' => $unitCost,
                'Total' => $revenueDetail->total_revenue,
            ];
        }
        return Datatables::of($output)->toJson();
    }


    public function getTotalData(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        // $fromDate = $request->input('from_date');
        // $toDate = $request->input('to_date');
        $client_ids = $request->input('client_id');

        $selectedDateFilter = $request->input('selectedDateFilter');

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
    
        $fromDate = null;
        $toDate = null;
    
        if ($fromDateRange && $toDateRange) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $toDate = $fromDate;
            }
        }

        $query = DB::table('stl_item_description')
            ->select(
                'stl_item_description.project_code',
                DB::raw('MAX(oms_order_creations.order_date) as order_date'),
                DB::raw('COUNT(*) as num_orders_completed'),
                DB::raw('SUM(stl_item_description.cost) as total_revenue')
            )
            ->join('oms_order_creations', 'stl_item_description.id', '=', 'oms_order_creations.process_id')
            ->join('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
            ->where('stl_item_description.billing_type_id', 1)
            ->where('oms_order_creations.status_id', 5)
            ->where('oms_order_creations.is_active', 1)
            ->whereIn('oms_order_creations.process_id', $processIds)
            ->groupBy('stl_client.client_name')
            ->groupBy('stl_client.client_no')
            ->groupBy('stl_item_description.project_code')
            ->groupBy('oms_order_creations.order_date')
            ->orderBy('stl_item_description.project_code');

        if ($fromDate && $toDate) {
                $query->whereBetween('oms_order_creations.order_date', [$fromDate, $toDate]);
        }

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('stl_client.id', $client_ids);
        }


        $revenueDetails = $query->get();

        $grandTotalRevenue = number_format((float)$revenueDetails->sum('total_revenue'), 2, '.', '');

        return response()->json(['GrandTotal' => $grandTotalRevenue]);
    }

// Manikandan
public function revenue_detail_process_fte(Request $request) {

    $user = Auth::user();

    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $client_ids = $request->input('client_id');
    // Add the new date filter logic here
    $selectedDateFilter = $request->input('selectedDateFilter');

    $fromDateRange = $request->input('from_date');
    $toDateRange = $request->input('to_date');

    $fromDate = null;
    $toDate = null;

    if ($fromDateRange && $toDateRange) {
        $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/';
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $toDate = $fromDate;
        }
    }

    if ($fromDate && $toDate && Carbon::parse($fromDate)->lte(Carbon::parse($toDate))) {

    $query = DB::table('service_audit AS sa')

      ->select(
        'sa.description_id AS service_id',
        'sa.description_id as id',
        'sa.process_name',
        'sid.project_code',
        'sc.client_name',
        'sa.cost AS unit_cost',
        'sa.no_of_resources',
        'sa.effective_date AS start_date',
        DB::raw('IFNULL((
            SELECT MIN(effective_date)
            FROM service_audit
            WHERE description_id = sa.description_id
              AND process_name = sa.process_name
              AND effective_date > sa.effective_date), NULL) AS end_date')
      )

      ->join('stl_item_description AS sid', 'sa.description_id', '=', 'sid.id')
      ->join('stl_client AS sc', 'sid.client_id', '=', 'sc.id')

      ->where('sa.is_active', 1)
      ->where('sid.is_active', 1)
      ->where('sid.billing_type_id', 2)
      ->whereIn('sa.description_id', $processIds)
            ->where('sa.effective_date', '<=', $toDate);

        if (!empty($request->input('client_id')) && $client_ids[0] !== 'All') {
        $query->whereIn('sc.id', $client_ids);
    }

    $auditRecords = $query->get();

    $output = [];
    foreach ($auditRecords as $key => $auditRecord) {

    $revenue_selected = 0;
            $start_date = Carbon::parse($fromDate)->greaterThan(Carbon::parse($auditRecord->start_date)) ? Carbon::parse($fromDate) : Carbon::parse($auditRecord->start_date);
            $end_date = Carbon::parse($toDate)->lessThan(Carbon::parse($auditRecord->end_date)) ? Carbon::parse($toDate) : Carbon::parse($auditRecord->end_date)->subDay();

    if ($end_date->lt($start_date)) {
        continue;
    }

      $unit_cost = $auditRecord->unit_cost;
      $no_of_resources = $auditRecord->no_of_resources;

      $days = $end_date->diffInDays($start_date);

            if (($key === count($auditRecords) - 1) || ($key < count($auditRecords) - 1)) {
        $days++;
      }

      if ($start_date->month === $end_date->month) {

        $revenue_selected = ($days / $start_date->daysInMonth) * $unit_cost * $no_of_resources;

        } else {
        $revenue_selected = 0;
        $current_month = $start_date->month;
        $current_year = $start_date->year;

        // Calculate revenue for the start month
            $revenue_selected += (($start_date->daysInMonth - $start_date->day + 1) / $start_date->daysInMonth) * $unit_cost * $no_of_resources;

        // Calculate revenue for the middle months
        while (true) {
            $current_month++;
            if ($current_month > 12) {
                $current_month = 1;
                $current_year++;
            }

            if ($current_year === $end_date->year && $current_month === $end_date->month) {
                break;
            }

            $revenue_selected += $unit_cost * $no_of_resources;
        }

        // Calculate revenue for the end month
            $revenue_selected += (($end_date->day) / $end_date->daysInMonth) * $unit_cost * $no_of_resources;
        }

        $output[] = [
            'id' => $auditRecord->service_id,
            'process_name' => $auditRecord->process_name,
            'project_code' => $auditRecord->project_code,
            'unit_cost' => number_format($unit_cost),
            'no_of_resources' => $no_of_resources,
            'expected_revenue' => number_format($no_of_resources * $unit_cost),
            'client_name' => $auditRecord->client_name,
            'start_date' => $start_date->format('m-d-Y'),
            'end_date' => $end_date->format('m-d-Y'),
            'days' => $days,
            'revenue_selected' => number_format($revenue_selected, 2, '.'),
        ];
        }

    return response()->json(['data' => $output]);

    } else {
        return response()->json(['data' => []]);
    }
}



public function revenue_detail_processDetail_fte(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $process_id =  $request->input('project_id');

    $query = DB::table('service_audit')
        ->select(
            'service_audit.id',
            'service_audit.description_id as id',
            'service_audit.process_name',
            'service_audit.unit_type_id',
            'service_audit.cost AS unit_cost',
            'service_audit.no_of_resources',
            'service_audit.effective_date'
        )
        ->join('stl_item_description', 'service_audit.description_id', '=', 'stl_item_description.id')
        ->where('service_audit.is_active', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.billing_type_id', 2)
        ->whereIn('service_audit.description_id', $processIds);

    if (!empty($process_id)) {
        $query->where('service_audit.description_id', $process_id);
    }

    $auditRecords = $query->get();

    // return response()->json($auditRecords, 200);

    $output = [];

    $cumulativeTotal = 0;
    $monthlyRevenue = 0; // Track monthly revenue
    $grandTotal = 0;

    foreach ($auditRecords as $key => $auditRecord) {
        $effective_date = Carbon::parse($auditRecord->effective_date);
        // Calculate end date based on next effective date or given end date
        $nextEffectiveDate = isset($auditRecords[$key + 1]) ? Carbon::parse($auditRecords[$key + 1]->effective_date)->subDay() : Carbon::today();
        $endDate = $nextEffectiveDate;
        $startDate = empty($fromDate) ? $effective_date : Carbon::parse($fromDate);
        // $startDate =  $effective_date ;
        if ($startDate->lt($effective_date)) {
            $startDate = $effective_date;
        }
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $daysInEffectiveMonth = $currentDate->daysInMonth;
            $daysRemaining = $endDate->diffInDays($currentDate) + 1;

            $perDayAmount = $auditRecord->unit_cost / $daysInEffectiveMonth;
            $invoiceAmount = $perDayAmount * $auditRecord->no_of_resources;

            if ($currentDate->day == 1) {
                $monthlyRevenue = 0;
            }

            $monthlyRevenue += $invoiceAmount;
            $cumulativeTotal += $invoiceAmount;
            $grandTotal += $invoiceAmount;

            if (empty($fromDate) || ($currentDate >= Carbon::parse($fromDate) && $currentDate <= Carbon::parse($toDate))) {
                $output[] = [
                    'process_name' => $auditRecord->process_name,
                    'Unit Cost' => $auditRecord->unit_cost,
                    'No of Resources' => $auditRecord->no_of_resources,
                    'per_day_amount' => number_format($perDayAmount, 2),
                    'invoice_amount' => number_format($invoiceAmount, 2),
                    'Eff Date' => $effective_date->format('Y-m-d'),
                    'Date' => $currentDate->format('Y-m-d'),
                    'days' => $daysRemaining,
                    'unit_cost_divided_by' => $daysInEffectiveMonth,
                    'Monthly Revenue' => number_format($monthlyRevenue, 2), // Monthly revenue
                    'Revenue Generated Till Date' => number_format($cumulativeTotal, 2)
                ];
                foreach ($output as &$row) {
                    $row['grand_total'] = number_format($grandTotal, 2);
                }
            }
            // Move to the next day
            $currentDate->addDay();
        }
    }

    // return response()->json($output);
    return Datatables::of($output)->toJson();
}

public function revenue_detail_process_total_fte(Request $request)
{
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $process_id =  $request->input('project_id');

    $query = DB::table('service_audit')
        ->select(
            'service_audit.id',
            'service_audit.description_id as id',
            'service_audit.process_name',
            'service_audit.unit_type_id',
            'service_audit.cost AS unit_cost',
            'service_audit.no_of_resources',
            'service_audit.effective_date'
        )
        ->join('stl_item_description', 'service_audit.description_id', '=', 'stl_item_description.id')
        ->where('service_audit.is_active', 1)
        ->where('stl_item_description.is_active', 1)
        ->where('stl_item_description.billing_type_id', 2)
        ->where('service_audit.description_id', $process_id);

    $auditRecords = $query->get();

    // Initialize grand total
    $grandTotal = 0;

    $output = [];

    foreach ($auditRecords as $key => $auditRecord) {
        $effective_date = Carbon::parse($auditRecord->effective_date);
        // Calculate end date based on next effective date or given end date
        $nextEffectiveDate = isset($auditRecords[$key + 1]) ? Carbon::parse($auditRecords[$key + 1]->effective_date)->subDay() : Carbon::today();
        $endDate = $nextEffectiveDate;
        $startDate = empty($fromDate) ? $effective_date : Carbon::parse($fromDate);
        if ($startDate->lt($effective_date)) {
            $startDate = $effective_date;
        }
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $daysInEffectiveMonth = $currentDate->daysInMonth;
            $perDayAmount = $auditRecord->unit_cost / $daysInEffectiveMonth;
            $invoiceAmount = $perDayAmount * $auditRecord->no_of_resources;
            if (empty($fromDate) || ($currentDate >= Carbon::parse($fromDate) && $currentDate <= Carbon::parse($toDate))) {
             $grandTotal += $invoiceAmount;
            }
            $currentDate->addDay();
        }
        // Add the current total to output array
        $output[] = [
            'Total' => number_format($grandTotal, 2),
        ];
    }

    $totalSum = array_sum(array_column($output, 'Total'));

    if (count($output) > 1) {
        $output[] = [
            'Total Sum' => number_format($totalSum, 2),
        ];
    }else{
        $output[] = [
            'Total Sum' => number_format($grandTotal, 2),
        ];
    }

    return response()->json(['Total' => $output[count($output) - 1]['Total Sum']]);
}

public function revenue_detail_client_fte(Request $request){

    $user = Auth::user();

    $processIds = $this->getProcessIdsBasedOnUserRole($user);
    $client_ids = $request->input('client_id');
    // Add the new date filter logic here
    $selectedDateFilter = $request->input('selectedDateFilter');
    // dd($selectedDateFilter);

    $fromDateRange = $request->input('from_date');
    $toDateRange = $request->input('to_date');

    $fromDate = null;
    $toDate = null;

    if ($fromDateRange && $toDateRange) {
        $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/';
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $toDate = $fromDate;
        }
    }


    if (isset($fromDate)) {
        $fromDate = Carbon::parse($fromDate);
    }
    if (isset($toDate)) {
        $toDate = Carbon::parse($toDate);
    }

    if ($fromDate->lte($toDate)) {

        $query = DB::table('service_audit AS sa')

          ->select(
            'sa.description_id AS service_id',
            'sa.description_id as id',
            'sa.process_name',
            'sid.project_code',
            'sc.client_name',
            'sa.cost AS unit_cost',
            'sa.no_of_resources',
            'sa.effective_date AS start_date',
            DB::raw('IFNULL((
                SELECT MIN(effective_date)
                FROM service_audit
                WHERE description_id = sa.description_id
                  AND process_name = sa.process_name
                  AND effective_date > sa.effective_date), NULL) AS end_date')
          )

          ->join('stl_item_description AS sid', 'sa.description_id', '=', 'sid.id')
          ->join('stl_client AS sc', 'sid.client_id', '=', 'sc.id')

          ->where('sa.is_active', 1)
          ->where('sid.is_active', 1)
          ->where('sid.billing_type_id', 2)
          ->whereIn('sa.description_id', $processIds)
        ->where('sa.effective_date', '<=', $toDate->format('Y-m-d'));

        if (!empty($client_ids) && $client_ids[0] !== 'All') {
            $query->whereIn('sc.id', $client_ids);
        }

        $auditRecords = $query->get();

        $output = [];
        foreach ($auditRecords as $key => $auditRecord) {

        $revenue_selected = 0;
        $start_date = $fromDate->greaterThan(Carbon::parse($auditRecord->start_date)) ? $fromDate : Carbon::parse($auditRecord->start_date);
        $end_date = $toDate->lessThan(Carbon::parse($auditRecord->end_date)) ? $toDate : Carbon::parse($auditRecord->end_date)->subDay() ;

        if ($end_date->lt($start_date)) {
            continue;
        }

          $unit_cost = $auditRecord->unit_cost;
          $no_of_resources = $auditRecord->no_of_resources;

          $days = $end_date->diffInDays($start_date);


          if (($key === count($auditRecords) - 1)||($key < count($auditRecords) - 1)) {
            $days++;
          }

          if ($start_date->month === $end_date->month) {

            $revenue_selected = ($days / $start_date->daysInMonth) * $unit_cost * $no_of_resources;

            } else {

                $revenue_selected = 0;
                $current_month = $start_date->month;
                $current_year = $start_date->year;
        
                // Calculate revenue for the start month
                    $revenue_selected += (($start_date->daysInMonth - $start_date->day + 1) / $start_date->daysInMonth) * $unit_cost * $no_of_resources;
        
                // Calculate revenue for the middle months
                while (true) {
                    $current_month++;
                    if ($current_month > 12) {
                        $current_month = 1;
                        $current_year++;
                    }
        
                    if ($current_year === $end_date->year && $current_month === $end_date->month) {
                        break;
                    }
        
                    $revenue_selected += $unit_cost * $no_of_resources;
                }
        
                // Calculate revenue for the end month
                    $revenue_selected += (($end_date->day) / $end_date->daysInMonth) * $unit_cost * $no_of_resources;

            }

            $output[] = [
                'id' => $auditRecord->service_id,
                'process_name' => $auditRecord->process_name,
                'project_code' => $auditRecord->project_code,
                'unit_cost' => $unit_cost,
                'no_of_resources' => $no_of_resources,
                'expected_revenue' => $no_of_resources * $unit_cost,
                'client_name' => $auditRecord->client_name,
                'start_date' => $start_date->format('m-d-Y'),
                'end_date' => $end_date->format('m-d-Y'),
                'days' => $days,
                'revenue_selected' => number_format($revenue_selected, 2, '.'),
            ];

        }
        $groupedOutput = [];
        foreach ($output as $record) {
            $clientName = $record['client_name'];
            if (!array_key_exists($clientName, $groupedOutput)) {
                $groupedOutput[$clientName] = 0;
            }
            $groupedOutput[$clientName] += floatval(str_replace(',', '', $record['revenue_selected']));
            
        }

        $finalOutput = [];
        foreach ($groupedOutput as $clientName => $totalRevenue) {
            $finalOutput[] = [
                'client_name' => $clientName,
                'total_revenue_selected' => number_format($totalRevenue, 2, '.'),
            ];
        }
        return response()->json(['data' => $finalOutput]);
        }
        else {
            return response()->json(['data' => []]);
        }

    }

    public function get_lob_dashboard(Request $request)
    {
        $client_id = $request->client_id;
        $product_id = $request->product_id;

        if (!is_array($client_id)) {
            $client_id = [$client_id];
        }
        if (in_array('All', $client_id)) {
            $getlob = DB::table('stl_lob')
                            ->select('id', 'name')
                            ->get();
        } else {
            $filteredLob = DB::table('stl_lob')->select('id', 'client_id', 'name')->get();
            $getlob = $filteredLob->filter(function($item) use ($client_id) {
            $clientIds = json_decode($item->client_id, true);
            // Check if there's any overlap between the request client_ids and the stored client_ids
            return !empty(array_intersect($client_id, $clientIds));
        });
        }

        if (in_array('All', $client_id)) {
            $getproduct = DB::table('stl_item_description')
                        ->select('id', 'process_name', 'project_code')
                        ->get();
        }else{
            $getproduct = DB::table('stl_item_description')
                        ->select('id', 'process_name', 'project_code')
                        ->whereIn('client_id', $client_id)
                        ->get();
        }


        return response()->json([
            'lob' => $getlob,
            'products' => $getproduct
        ]); 
    }

    public function get_process_dashboard(Request $request)
    {
        $lob_id = $request->lob_id;
        $client_id = $request->client_id;

        if (!is_array($lob_id)) {
            $lob_id = [$lob_id];
        }
        if (in_array('All', $lob_id)) {
            $getprocess = DB::table('stl_process')
                            ->select('id', 'name')
                            ->get();
        } else {
            $getprocess = DB::table('stl_process')
                            ->select('id', 'name', 'lob_id')
                            ->whereIn('lob_id', $lob_id)
                            ->get();

        }

    if($lob_id && $client_id){
        $get_product = DB::table('stl_item_description')
                    ->select('id', 'process_name', 'project_code')
                    ->whereIn('client_id', $client_id)
                    ->whereIn('lob_id', $lob_id)
                    ->get();
    }
    
        return response()->json([
            'process' => $getprocess,
            'products' => $get_product
        ]); 
    }

    public function get_product_dashboard(Request $request)
    {
        $process_type_id = $request->process_type_id;
        $client_id = $request->client_id;
        $lob_id = $request->lob_id;

        if (!is_array($process_type_id)) {
            $process_type_id = [$process_type_id];
        }
        if (!is_array($client_id)) {
            $client_id = [$client_id];
        }
        if (!is_array($lob_id)) {
            $lob_id = [$lob_id];
        }
        if (in_array('All', $process_type_id)) {
            $getprocess = DB::table('stl_item_description')
                            ->select('id', 'process_name', 'project_code')
                            ->whereIn('client_id', $client_id)
                            ->whereIn('lob_id', $lob_id)
                            ->get();
        } else {
            $getprocess = DB::table('stl_item_description')
                            ->select('id', 'process_name', 'project_code')
                            ->whereIn('process_id', $process_type_id)
                            ->whereIn('client_id', $client_id)
                            ->whereIn('lob_id', $lob_id)
                            ->get();
        }

        return response()->json($getprocess); 
    }
    



    public function pending_status(Request $request)
    {
        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);
 
        $today = Carbon::now();
   
 
   
        $status_name = DB::table('oms_status')->select('id', 'status')->get()->pluck('status', 'id')->toArray();
         
        $result = [];
 
        foreach ($status_name as $status_id => $status_name_value) {
            if($status_id != 5){
                $tenDaysAgo = $today->copy()->subDays(10);
                $twentyDaysAgo = $today->copy()->subDays(20);
                $thirtyDaysAgo = $today->copy()->subDays(30);
 
                $moreThan10Days = OrderCreation::where('status_id', $status_id)
                    ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
                    ->whereBetween('status_updated_time', [$twentyDaysAgo, $tenDaysAgo])
                    ->whereIn('oms_order_creations.process_id', $processIds);
 
                $moreThan10DaysCount = $moreThan10Days->count();
 
 
                $moreThan20Days = OrderCreation::where('status_id', $status_id)
                    ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
                    ->whereBetween('status_updated_time', [$thirtyDaysAgo, $twentyDaysAgo])
                    ->whereIn('oms_order_creations.process_id', $processIds);
 
                $moreThan20DaysCount = $moreThan20Days->count();
               
   
 
 
            $moreThan30Days = OrderCreation::where('status_id', $status_id)
            ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
            ->where('status_updated_time', '<=', $thirtyDaysAgo)
            ->whereIn('oms_order_creations.process_id', $processIds);
           
 
        $moreThan30DaysCount = $moreThan30Days->count();
 
 
        if ($moreThan10DaysCount > 0 || $moreThan20DaysCount > 0 || $moreThan30DaysCount > 0) {
            $result[] = [
                'status' => $status_name_value,
                'moreThan10Days' => $moreThan10DaysCount,
                'moreThan20Days' => $moreThan20DaysCount,
                'moreThan30Days' => $moreThan30DaysCount,
            ];
        }
    }
 
           
        }
   
        return Datatables::of($result)->toJson();
    }

    public function total_users() {
        $user = User::where('id', Auth::id())->first();
        // Assuming getAllLowerLevelUserIds returns an array of user IDs
        $user_lower_ids = User::getAllLowerLevelUserIds(Auth::id());
        
        // Filter out the current user's ID
        $user_lower_ids = array_filter($user_lower_ids, function($id) use ($user) {
            return $id != $user->id;
        });
        
        // Get the count of active users
        $active_user = User::whereIn('id', $user_lower_ids)
                           ->where('logged_in', 1)
                           ->count();
    
        // Get the count of lower level users
        $user_lower_count = count($user_lower_ids);
    
        return response()->json([
            'user_lower_count' => $user_lower_count,
            'active_user' => $active_user,
        ]);
    }



    public function total_users_name() {
        // Get the current user
        $currentUserId = Auth::id();
        $user = User::find($currentUserId);
    
        // Get all lower level user IDs
        $user_lower_ids = User::getAllLowerLevelUserIds($currentUserId);
    
        // Filter out the current user's ID
        $user_lower_ids = array_filter($user_lower_ids, function($id) use ($currentUserId) {
            return $id != $currentUserId;
        });
    
        // Get active lower-level users
        $active_users = User::whereIn('id', $user_lower_ids)
            ->where('logged_in', 1) // Filter to get only logged-in users
            ->get(['id', 'emp_id', 'username', 'logged_in']);
        
            return response()->json([
                'data' => $active_users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'emp_id' => $user->emp_id,
                        'username' => $user->username
                    ];
                }),
            ]);
    }
    

    
public function tat_zone_count(Request $request) {
    $user = Auth::user();
    $processIds = $this->getProcessIdsBasedOnUserRole($user);

    $client_id = $request->input('client_id');
    $project_id = $request->input('project_id');

    if (!is_array($client_id)) {
        $client_id = [$client_id];
    }
    if (!is_array($project_id)) {
        $project_id = [$project_id];
    }

    $selectedDateFilter = $request->input('selectedDateFilter');
    

    $fromDateRange = $request->input('from_date');
    $toDateRange = $request->input('to_date');

    $fromDate = null;
    $toDate = null;

    if ($fromDateRange && $toDateRange) {
        $fromDate = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
        $toDate = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
    } else {
        $datePattern = '/(\d{2}-\d{2}-\d{4})/';
        if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
            list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
            $fromDateText = trim($fromDateText);
            $toDateText = trim($toDateText);
            preg_match($datePattern, $fromDateText, $fromDateMatches);
            preg_match($datePattern, $toDateText, $toDateMatches);
            $fromDate = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
            $toDate = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
        } else {
            preg_match($datePattern, $selectedDateFilter, $dateMatches);
            $fromDate = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
            $toDate = $fromDate;
        }
    }


    $tatstatusCountsQuery = DB::table('oms_order_creations')
        ->leftJoin('stl_item_description', 'oms_order_creations.process_id', '=', 'stl_item_description.id')
        ->leftJoin('oms_state', 'oms_order_creations.state_id', '=', 'oms_state.id')
        ->leftJoin('county', 'oms_order_creations.county_id', '=', 'county.id')
        ->leftJoin('oms_status', 'oms_order_creations.status_id', '=', 'oms_status.id')
        ->leftJoin('stl_client', 'stl_item_description.client_id', '=', 'stl_client.id')
        ->leftJoin('oms_users as assignee_users', 'oms_order_creations.assignee_user_id', '=', 'assignee_users.id')
        ->leftJoin('oms_users as assignee_qas', 'oms_order_creations.assignee_qa_id', '=', 'assignee_qas.id')
        ->leftJoin('oms_users as typist_users', 'oms_order_creations.typist_id', '=', 'typist_users.id')
        ->leftJoin('oms_users as typist_qas', 'oms_order_creations.typist_qc_id', '=', 'typist_qas.id')
        ->leftJoin('oms_users as associate_names', 'oms_order_creations.associate_id', '=', 'associate_names.id')
        ->leftJoin('stl_lob', 'stl_item_description.lob_id', '=', 'stl_lob.id')
        ->leftJoin('oms_tier','oms_order_creations.tier_id', '=', 'oms_tier.id')
        ->leftJoin('stl_process', 'oms_order_creations.process_type_id', '=', 'stl_process.id')
        ->select(
            'oms_order_creations.id',
            'oms_order_creations.order_id as order_id',
            'oms_order_creations.status_id as status_id',
            'oms_order_creations.order_date as order_date',
            'stl_item_description.tat_value as tat_value',
            'oms_order_creations.assignee_user_id',
            'oms_order_creations.assignee_qa_id',
            'oms_order_creations.typist_id',
            'oms_order_creations.typist_qc_id',
            'oms_order_creations.associate_id',
            'stl_client.id as client_id',
        )
        ->whereIn('oms_order_creations.process_id', $processIds)
        ->where('oms_order_creations.is_active', 1)
        ->whereNotNull('oms_order_creations.assignee_user_id')
        ->where('stl_item_description.is_approved', 1)
        ->where('stl_client.is_approved', 1);



    if (!in_array($user->user_type_id, [1, 2, 3, 4, 5, 9])) {
        if ($user->user_type_id == 6) {
            $tatstatusCountsQuery->where('oms_order_creations.assignee_user_id', $user->id);
        } elseif($user->user_type_id == 7) {
            $tatstatusCountsQuery->where('oms_order_creations.assignee_qa_id', $user->id)
            ->whereNotIn('oms_order_creations.status_id', [1]);
        } elseif($user->user_type_id == 8) {
            $tatstatusCountsQuery->where(function ($query) use($user) {
                $query->where('oms_order_creations.assignee_user_id', $user->id)
                    ->orWhere('oms_order_creations.assignee_qa_id', $user->id);
            });
        } elseif($user->user_type_id == 10){
            $tatstatusCountsQuery->where('oms_order_creations.typist_id', $user->id)
            ->whereNotIn('oms_order_creations.status_id', [1, 13, 4, 15, 17]);
        } elseif($user->user_type_id == 11){
            $tatstatusCountsQuery->where('oms_order_creations.typist_qc_id', $user->id)
            ->whereNotIn('oms_order_creations.status_id', [1, 13, 4, 15, 16]);
        }
    }

    if ($fromDate && $toDate) {
        $tatstatusCountsQuery->whereBetween('oms_order_creations.order_date', [
            $fromDate . ' 00:00:00',
            $toDate . ' 23:59:59'
        ]);
    }

    if (!empty($client_id) && $client_id[0] !== 'All') {
        $tatstatusCountsQuery->whereIn('stl_client.id', $client_id);
    }

    if (!empty($project_id) && $project_id[0] !== 'All') {
        $tatstatusCountsQuery->whereIn('oms_order_creations.process_id', $project_id);
    }

    $tatStatusCountsQuery = $tatstatusCountsQuery->get();

    function calculateTatValues($tatStatusCountsQuery)
    {
        $resultsByStatus = [];

        foreach ($tatStatusCountsQuery as $order) {
            if (!is_null($order->tat_value)) {
                $tatValue = $order->tat_value;
                $statusId = $order->status_id; 
                $tatHours = $tatValue / 4;

                $orderDate = new \DateTime($order->order_date, new \DateTimeZone('America/New_York'));

                $currentDate = new \DateTime('now', new \DateTimeZone('America/New_York'));

                $diff = $currentDate->diff($orderDate);
                $hoursDifference = ($diff->days * 24) + $diff->h + ($diff->i / 60);

                if (!isset($resultsByStatus[$statusId])) {
                    $resultsByStatus[$statusId] = [
                        'orderReachfirst' => 0,  
                        'orderReachsecond' => 0, 
                        'orderReachthird' => 0,  
                        'orderReachfourth' => 0, 
                        'orderReachedtatvalue' => 0, 
                    ];
                }
                if ($statusId == 5) {
                    continue;
                }
                if($hoursDifference >= $tatHours * 4){
                    $resultsByStatus[$statusId]['orderReachedtatvalue'] += 1;
                }elseif ($hoursDifference >= $tatHours * 3) {
                    $resultsByStatus[$statusId]['orderReachfourth'] += 1;
                }elseif ($hoursDifference >= $tatHours * 2) {
                    $resultsByStatus[$statusId]['orderReachthird'] += 1;
                }elseif ($hoursDifference >= $tatHours * 1) {
                    $resultsByStatus[$statusId]['orderReachsecond'] += 1;
                }else{
                    $resultsByStatus[$statusId]['orderReachfirst'] += 1;
            }
                
            }
        }

        return $resultsByStatus;
    }

    $results = calculateTatValues($tatStatusCountsQuery);
        $totalFirstCount = array_sum(array_column($results, 'orderReachfirst'));
        $totalSecondCount = array_sum(array_column($results, 'orderReachsecond'));
        $totalThirdCount = array_sum(array_column($results, 'orderReachthird'));
        $totalFourthCount = array_sum(array_column($results, 'orderReachfourth'));
        $totalReachedTatValueCount = array_sum(array_column($results, 'orderReachedtatvalue'));

    return response()->json([
            'reachedtat_count' => $totalReachedTatValueCount .','. ' Out of TAT',
            'red_count' => $totalFourthCount .','. ' Super Rush',
            'orange_count' => $totalThirdCount .','. ' Rush', 
            'blue_count' => $totalSecondCount .','. ' Priority',
            'green_count' => $totalFirstCount .','. ' Non Priority', 
        ]);        
           
    }

    public function carry_over_monthly_count(Request $request){

        $user = Auth::user();
        $processIds = $this->getProcessIdsBasedOnUserRole($user);

        $client_id = $request->input('client_id');
        $project_id = $request->input('project_id');
        $selectedDateFilter = $request->input('selectedDateFilter');
    
        $currentDate = Carbon::now();
        $previousDate = Carbon::now()->subDay();

        $fromDateRange = $request->input('from_date');
        $toDateRange = $request->input('to_date');
   
        $from_date = null;
        $to_date = null;
   
        if ($fromDateRange && $toDateRange) {
            $from_date = Carbon::createFromFormat('Y-m-d', $fromDateRange)->toDateString();
            $to_date = Carbon::createFromFormat('Y-m-d', $toDateRange)->toDateString();
        } else {
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (!empty($selectedDateFilter) && strpos($selectedDateFilter, 'to') !== false) {
                list($fromDateText, $toDateText) = explode('to', $selectedDateFilter);
                $fromDateText = trim($fromDateText);
                $toDateText = trim($toDateText);
                preg_match($datePattern, $fromDateText, $fromDateMatches);
                preg_match($datePattern, $toDateText, $toDateMatches);
                $from_date = isset($fromDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $fromDateMatches[1])->toDateString() : null;
                $to_date = isset($toDateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $toDateMatches[1])->toDateString() : null;
            } else {
                preg_match($datePattern, $selectedDateFilter, $dateMatches);
                $from_date = isset($dateMatches[1]) ? Carbon::createFromFormat('m-d-Y', $dateMatches[1])->toDateString() : null;
                $to_date = $from_date;
            }
        }

        if (!is_array($project_id)) {
            $project_id = explode(',', $project_id); 
        }

        if (!is_array($client_id)) {
            $client_id = explode(',', $client_id); 
        }


        $statusCountsQuery = OrderCreation::query()->with('process', 'client')
        ->whereHas('process', function ($query) {
            $query->where('stl_item_description.is_approved', 1);
        })
        ->whereHas('client', function ($query) {
            $query->where('stl_client.is_approved', 1);
        });

        $statusCountsQuery2 = clone $statusCountsQuery;
        $statusCountsQuery3 = clone $statusCountsQuery;
        


        //daily
        $statusCountsQuery4 = clone $statusCountsQuery;
        $statusCountsQuery5 = clone $statusCountsQuery;

//


        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $carry_forward= $statusCountsQuery->with('process', 'client')
                ->whereIn('process_id', $processIds)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('status_id', '!=', 3)
                ->where('completion_date', null)
                ->whereDate('order_date', '<', $from_date)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $carry_forward = $statusCountsQuery->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('status_id', '!=', 3)
                ->where('completion_date', null)
                ->whereDate('order_date', '<', $from_date);
                // dd('jo');
            } else {
                // Case: project_id is 'All'
                $carry_forward = $statusCountsQuery->whereIn('process_id', $processIds)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('status_id', '!=', 3)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '<', $from_date);
            }
        }

        $carry_forward = $carry_forward->count();





        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $received = $statusCountsQuery2->whereIn('process_id', $processIds)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('completion_date', null)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $received = $statusCountsQuery2->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('completion_date', null)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date)->get();
            } else {
                // Case: project_id is 'All'
                $received = $statusCountsQuery2->whereIn('process_id', $processIds)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '>=', $from_date)
                    ->whereDate('order_date', '<=', $to_date);
            }
        }


        $received = $received->count();



        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $completed = $statusCountsQuery3
            ->whereDate('completion_date', '>=', $from_date)
            ->whereDate('completion_date', '<=', $to_date)
            ->whereDate('order_date', '>=', $from_date)
            ->whereDate('order_date', '<=', $to_date)
            ->whereIn('process_id', $processIds)
            ->where('status_id', 5)
            ->where('is_active', 1)
            ->whereHas('process', function ($query) use ($client_id) {
                $query->whereIn('client_id', $client_id);
            });
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $completed = $statusCountsQuery3
                ->whereDate('completion_date', '>=', $from_date)
                ->whereDate('completion_date', '<=', $to_date)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date)
                ->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('status_id', 5)
                ->where('is_active', 1);
            } else {
                // Case: project_id is 'All'
                $completed = $statusCountsQuery3
                ->whereDate('completion_date', '>=', $from_date)
                ->whereDate('completion_date', '<=', $to_date)
                ->whereDate('order_date', '>=', $from_date)
                ->whereDate('order_date', '<=', $to_date)
                ->whereIn('process_id', $processIds)
                ->where('status_id', 5)
                ->where('is_active', 1);
            }
        }


        $completed = $completed->count();
    
    

        $pending = $carry_forward + $received - $completed;

        if ($pending < 0) {
            $pending = 0;
        }


        //daily


        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $daily_carry_forward = $statusCountsQuery4->with('process', 'client')
                ->whereIn('process_id', $processIds)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('status_id', '!=', 3)
                ->where('completion_date', null)
                ->whereDate('order_date', '=', $previousDate);
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $daily_carry_forward = $statusCountsQuery4->whereIn('process_id', $processIds)
                    ->whereIn('process_id', $project_id)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('status_id', '!=', 3)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '=', $previousDate);
            } else {
                // Case: project_id is 'All'
                $daily_carry_forward = $statusCountsQuery4->whereIn('process_id', $processIds)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('status_id', '!=', 3)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '=', $previousDate);
            }
        }

        $daily_carry_forward = $daily_carry_forward->count();




        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $daily_received = $statusCountsQuery2->whereIn('process_id', $processIds)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('completion_date', null)
                ->whereDate('order_date', '=', $currentDate);
        } else {
            if (!in_array('All', $project_id)) {
                // Case: project_id is specified (not 'All')
                $daily_received = $statusCountsQuery2->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('is_active', 1)
                ->where('status_id', '!=', 5)
                ->where('completion_date', null)
                ->whereDate('order_date', '=', $currentDate);
            } else {
                // Case: project_id is 'All'
                $daily_received = $statusCountsQuery2->whereIn('process_id', $processIds)
                    ->where('is_active', 1)
                    ->where('status_id', '!=', 5)
                    ->where('completion_date', null)
                    ->whereDate('order_date', '=', $currentDate);
            }
        }


        $daily_received = $daily_received->count();





        if (in_array('All', $project_id) && !in_array('All', $client_id)) {
            // Case: Project_id is 'All' and client_id is not 'All'
            $daily_completed = $statusCountsQuery5
            ->whereDate('order_date', '>=', $previousDate)
            ->whereDate('completion_date', '=', $currentDate)
            ->whereIn('process_id', $processIds)
            ->where('status_id', 5)
            ->where('is_active', 1)
            ->whereHas('process', function ($query) use ($client_id) {
                $query->whereIn('client_id', $client_id);
            });
        } else {
            if (!in_array('All', $project_id)) {
                $daily_completed = $statusCountsQuery5
                ->whereDate('order_date', '>=', $previousDate)
                ->whereDate('completion_date', '=', $currentDate)
                ->whereIn('process_id', $processIds)
                ->whereIn('process_id', $project_id)
                ->where('status_id', 5)
                ->where('is_active', 1)
                ->whereHas('process', function ($query) use ($client_id) {
                    $query->whereIn('client_id', $client_id);
                });
            } else {
                $daily_completed = $statusCountsQuery5
                ->whereDate('order_date', '>=', $previousDate)
                ->whereDate('completion_date', '=', $currentDate)
                ->whereIn('process_id', $processIds)
                ->where('status_id', 5)
                ->where('is_active', 1);
            }
        }


        $daily_completed = $daily_completed->count();

        $daily_pending = $daily_carry_forward + $daily_received - $daily_completed;

        if ($daily_pending < 0) {
            $daily_pending = 0;
        }
       
        return response()->json([
            'data' => [
                [
                    'monthLabel' => 'MONTHLY',
                    'carry_forward' => $carry_forward,
                    'received' => $received,
                    'completed' => $completed,
                    'pending' => $pending,
                ],
                [
                    'monthLabel' => 'DAILY',
                    'carry_forward' => $daily_carry_forward,
                    'received' => $daily_received,
                    'completed' => $daily_completed,
                    'pending' => $daily_pending,

                ]
            ]
        ]);    


    }

    public function resourceTable() {
        
        $currentUserId = Auth::id();
        $user = User::find($currentUserId);
        
        $user_lower_ids = User::getAllLowerLevelUserIds($currentUserId);
    
        $user_lower_ids = array_filter($user_lower_ids, function($id) use ($currentUserId) {
            return $id != $currentUserId;
        });

        $active_users = User::select('oms_users.id', 'oms_users.emp_id', 'oms_users.username', 'oms_users.logged_in', 'reporting_user.username as reporting_username')
        ->leftJoin('oms_users as reporting_user', 'oms_users.reporting_to', '=', 'reporting_user.id')
        ->whereIn('oms_users.id', $user_lower_ids)
        ->get();
    
        
        return response()->json([
            'data' => $active_users->map(function($user) {
                return [
                    'id' => $user->id,
                    'emp_id' => $user->emp_id,
                    'username' => $user->username,
                    'status' => $user->logged_in ? 'Available' : 'UnAvailable',
                    'reporting_to' => $user->reporting_username ?? 'N/A' 
                ];
            }),
        ]);
    }
    


}
