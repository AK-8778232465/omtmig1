<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserImpersonateController extends Controller
{
    public function index(){
        $userList = User::with('usertypes:id,user_types')->get();
        
        return view('impersonate', compact('userList'));
   }

   public function impersonate($id){
        Auth::loginUsingId($id);

        $user = User::with('departments')->where('id', Auth::id())->first();

          session(['uid' => Auth::id()]);
          session(['user_type_id' => $user->user_type_id]);
          session(['is_approver' => $user->is_approver]);
          session(['department_id' => $user->department_id]);
          session(['company_id' => isset($user->departments->company_id) ? $user->departments->company_id : 0]);
        
        return response()->json(['success' => true]);
   }
}
