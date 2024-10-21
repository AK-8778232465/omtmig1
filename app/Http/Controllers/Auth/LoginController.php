<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }

    // protected function credentials(Request $request)
    // {
    //     return array_merge($request->only($this->username(), 'password'), ['is_active' => 1]);
    // }

    protected function authenticated(Request $request, $user)
    {
        $user = User::where('id', Auth::id())->first();
        $user->update(['logged_in' => 1]);
        return redirect()->intended('/home');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function attemptLogin(Request $request)
    {
        $loginIdentifier = $request->input($this->username());
        $credentials = $request->only($this->username(), 'password');
        $credentials['is_active'] = 1;

        if (strpos($loginIdentifier, 'SIPL') === 0 || strpos($loginIdentifier, 'SIPD') === 0) {
            return $this->guard()->attempt(['emp_id' => $loginIdentifier, 'password' => $credentials['password'], 'is_active' => 1]);
        } elseif (strpos($loginIdentifier, '@') !== false) {
            return $this->guard()->attempt($credentials);
        }

        return false;
    }



    public function logout(){
        $user = User::where('id', Auth::id())->first();

        if ($user) {
            User::where('id', $user->id)->update(['logged_in' => 0]); 
        }

        Auth::logout(); 
        return redirect('/login');

    }


}
