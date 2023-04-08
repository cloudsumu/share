<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Return the Agent Profile Page.
     * 
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('agent.account.profile');
    }

    /**
     * Update User Profile
     *
     * @param  \App\Provider $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'mobile' => 'required|string|min:8|max:14',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'address' => 'sometimes|string',
        ]);

        try {
            $agent = Agent::findOrFail(auth('agent')->user()->id);
            $agent->name = $request->name;
            $agent->mobile = $request->mobile;
            $agent->company = $request->company;
            $agent->language = $request->language;
            $agent->address = $request->address;

            if ($request->hasFile('avatar')) {
                if($agent->avatar) {
                    Storage::delete($agent->avatar);
                }
                $agent->avatar = $request->avatar->store('public/agent/avatars');
            }

            $agent->update();

            return redirect()->back()->with('flash_success', trans('admin.profile_update'));
        } catch (Exception $e) {
            Log::error("Agent profile Update Error.");
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Change Agent Password
     *
     * @param  \App\Provider $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {

        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        try {

            $agent = Agent::find(Auth::guard('agent')->user()->id);

            if (password_verify($request->old_password, $agent->password)) {
                $agent->password = bcrypt($request->password);
                $agent->update();

                return redirect(route('agent.profile').'#changePassword')
                    ->withSuccess(trans('Password Updated Successfully.'));
            } else {
                return redirect(route('agent.profile').'#changePassword')
                    ->withErrors(trans('Passwords Do Not Match'));
            }
        } catch (Exception $e) {
            Log::warning("Agent ".$agent->id." Password Change Error:- ".$e->getMessage());
            return redirect(route('agent.profile').'#changePassword')
                ->withErrors(trans('Sometime Went Wrong'));
        }
    }
}
