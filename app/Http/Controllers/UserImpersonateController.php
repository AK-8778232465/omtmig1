<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class UserImpersonateController extends Controller
{
    public function index(){
        $userList = User::with('stl_usertype:id,usertype')->get();
        
        return view('impersonate', compact('userList'));
   }

   public function impersonate($id){
        Auth::loginUsingId($id);

        $user = User::with('departments')->where('id', Auth::id())->first();

          session(['uid' => Auth::id()]);
          session(['user_type_id' => $user->user_type_id]);
          session(['is_approver' => $user->is_approver]);
          // session(['department_id' => $user->usertype]);
          session(['company_id' => isset($user->departments->company_id) ? $user->departments->company_id : 0]);
        
        return response()->json(['success' => true]);
   }

   public function changeRole(Request $request){
   
     $availableRoles = DB::table('oms_user_profiles')->where('oms_user_id', Auth::id())->pluck('user_type_id')->toArray();
// dd($request->user_type_id);
     if(in_array($request->user_type_id,  $availableRoles)) {
          DB::table('oms_users')->where('id',Auth::id())->update(['user_type_id' =>$request->user_type_id ]);
     }
     $id = Auth::id();

     $user = User::find($id);
     DB::table('model_has_roles')->where('model_id',$id)->delete();
     $user->assignRole($request->user_type_id );

     Auth::loginUsingId($id);

     $user = User::where('id', Auth::id())->first();

       session(['uid' => Auth::id()]);
       session(['user_type_id' => $user->user_type_id]);
       session(['is_approver' => $user->is_approver]);
       // session(['department_id' => $user->usertype]);
       session(['company_id' => 0]);
     
     return response()->json(['success' => true]);
}
}
