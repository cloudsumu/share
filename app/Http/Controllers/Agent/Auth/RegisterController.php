<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Models\Agent;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest:agent')->only('showRegistrationForm');
    }

    /**
     * Where to redirect agent after registration.
     * 
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::AGENT_HOME;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('agent.auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $credentials = $request->except(['password_confirmation', 'terms']);
        $countryCodeLength = strlen($credentials['country_code'])+1; // For + sign in the mobile number.
        $credentials['mobile'] = substr($credentials['mobile'], $countryCodeLength, strlen($credentials['mobile'])-$countryCodeLength);

        $credentials['password'] = Hash::make($credentials['password']);

        $agent = Agent::create($credentials);

        $this->guard()->login($agent);

        if ($response = $this->registered($request, $agent)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }
    
    /**
     * Get a validator for an incoming registration request.
     * 
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'mobile' => 'required',
            'country_code' => 'required',
            'email' => 'sometimes|email|max:255',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);
    }

    /**
     * Get the guard to be used during Registration.
     * 
     * @return Illuminate\Contracts\Auth\StatefulGuard;
     */
    public function guard()
    {
        return Auth::guard('agent');
    }

}
