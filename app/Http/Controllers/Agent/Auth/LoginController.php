<?php

namespace App\Http\Controllers\Agent\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Instance a new Controller Instance
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:agent')->except('logout');
        $this->middleware('auth:agent')->only('logout');
    }
    
    /**
     * Where to redirect user after login
     * 
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::AGENT_HOME;

    /**
     * Show the agent's login form.
     * 
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm() {
        return view('agent.auth.login');
    }

    public function guard()
    {
        return Auth::guard('agent');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('agent.login'));
    }
}
