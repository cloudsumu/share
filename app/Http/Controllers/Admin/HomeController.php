<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Dispute;
use App\Models\Setting;
use App\Models\Provider;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\UserRequestRating;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ChangePasswordRequest;
use Edujugon\PushNotification\PushNotification;
use App\Http\Requests\Admin\UpdateProfileRequest;

class HomeController extends Controller
{
    /**
     * Return the Admin Dashboard.
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $admin = auth()->user('admin');
        $roles = $admin->getRoleNames()->toArray();

        if(in_array('dispute-manager', $roles)) {
            return redirect()
                ->route('admin.dispute.index');
        }

        $user_count     = User::count();
        $agent_count    = Agent::count();
        $provider_count = Provider::count();
        $dispute_count  = Dispute::count();
        $requests       = UserRequest::latest()->paginate(6);
        $user_request_loc = UserRequest::select('s_latitude', 's_longitude')
                        ->whereNotNull('s_latitude')
                        ->get();
        
        $req = '';

        foreach($user_request_loc as $u) {
            $req = $req."{coords:[".$u->s_latitude.",".$u->s_longitude."]},";
        }

        $all_rides = UserRequest::select('id')
                    ->whereNotNull('provider_id')
                    ->whereNotIn('status', ['CANCELLED'])
                    ->get();

        $rides_count = $all_rides->count();

        $revenue = UserRequestPayment::whereIn('request_id', $all_rides)->sum('total');
        $cancelled_rides = UserRequest::where('status', 'CANCELLED')->count();
        $completed_rides = UserRequest::where('status', 'COMPLETED')->count();

        return view('admin.dashboard', compact('revenue', 'user_count', 'cancelled_rides', 'agent_count', 'provider_count', 'dispute_count', 'requests', 'req', 'rides_count', 'completed_rides'));
    }

    public function dispatcher()
    {
        return view('admin.dispatcher_dashboard');
    }
    
    /**
     * Return the Admin Dashboard.
     *
     * @return Illuminate\Http\Response
     */
    // public function pushtest(Request $request)
    // {
    //     try {
    //         $push   = new PushNotification('fcm');
    //         $message=$push->setMessage(
    //             ['notification' => $request->push_message, ]
    //         )->setDevicesToken([$request->device_token])->send()->setApiKey('AAAAl4Hyffc:APA91bEGmABGLK7PPIo2epUGNrZic3DMmVHOu8S4kOkar-HfVyuCNIfl82atIGWIU5APhZ2EwPCxb6LKO6SH9HnwDBYTyGZwfqJ6WMx_q0ppQyxUfxQLX_1HJ7YrnCKIbeClOkC9Kojp')
    //         ->setConfig(['dry_run' => false]);
    //         //dd($push->getFeedback());
    //         if ($push->getFeedback()->success) {
    //             $message = 'sent';
    //         } else {
    //             $message = 'error';
    //         }
    //         Log::alert('push hit android' . $message);

    //         return $message;
    //     } catch (\Throwable $th) {
    //         Log::alert('push hit' . $th->getMessage());
    //     }
    // }

    /**
     * Return the Settings Page.
     *
     * @return Illuminate\Http\Response
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Return the Profile Settings Page.
     *
     * @return Illuminate\Http\Response
     */
    public function profileSettings()
    {
        return view('admin.profile_settings');
    }

    public function tnc()
    {
        $page = 'tnc';
        return view('admin.tnc', compact('page'));
    }

    public function saveTnc(Request $request)
    {
        if (strlen($request->tnc) < 65535) {
            $find = Setting::where('key', 'tnc');
            if ($find->count() == 0) {
                // Does Not exist create an entry in db.
                try {
                    $setting        = new Setting();
                    $setting->key   = 'tnc';
                    $setting->value = $request->tnc;
                    $setting->save();
                } catch (\Illuminate\Database\QueryException $e) {
                    return redirect(route('admin.settings.tnc'))
                        ->withErrors('Some Error Occurred.');
                }
            } else {
                $find = Setting::where('key', 'tnc')->update(['value' => $request->tnc]);
            }

            return redirect(route('admin.settings.tnc'))
                ->withSuccess(__('crud.general.tnc') . ' ' . __('crud.general.updated'));
        }
    }

    public function privacy(Request $request)
    {
        $page = 'privacy';
        return view('admin.tnc', compact('page'));
    }

    public function savePrivacy(Request $request)
    {
        if (strlen($request->privacy) < 65535) {
            $find = Setting::where('key', 'privacy');
            if ($find->count() == 0) {
                // Does Not exist create an entry in db.
                try {
                    $setting        = new Setting();
                    $setting->key   = 'privacy';
                    $setting->value = $request->tnc;
                    $setting->save();
                } catch (\Illuminate\Database\QueryException $e) {
                    return redirect(route('admin.settings.privacy'))
                        ->withErrors('Some Error Occurred.');
                }
            } else {
                $find = Setting::where('key', 'privacy')->update(['value' => $request->tnc]);
            }

            return redirect(route('admin.settings.privacy'))
                ->withSuccess(__('crud.general.privacy_policy') . ' ' . __('crud.general.updated'));
        }
    }

    public function language($lang = 'en')
    {
        
                try {
                    $admin = Admin::find(auth()->user()->id);
                    $admin->update(['language' => $lang]);
                    \App::setLocale($lang);
                    return redirect()->back()
                    ->with('success', __('crud.general.updated'));
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    return redirect()->back()->withErrors('Some Error Occurred.');
                        //route('admin.home')
                }

    }

    public function ratings()
    {
        $ratings = UserRequestRating::latest()
                    ->paginate();
        return view('admin.ratings', compact('ratings'));
    }

    public function requestHistory(Request $request)
    {
        $search       = $request->get('search', '');
        $userRequests = UserRequest::search($search)
                            ->latest()
                            ->paginate();

        return view('admin.requestHistory', compact('userRequests'));
    }

    public function requestDetail(UserRequest $userRequest)
    {
        return view('admin.requestDetail_template', compact('userRequest'));
    }

    /**
     * Updates the Admin Profile
     * @return Illuminate\Http\Response
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $credentials = $request->validated();
        $admin       = $request->user('admin');

        if ($request->hasFile('avatar')) {
            if ($admin->avatar) {
                Storage::delete($admin->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/admin/avatars');
        }

        $admin->update($credentials);

        return redirect(route('admin.profileSettings') . '#general')
            ->with('success', __('crud.navlinks.profile') . ' ' . __('crud.general.updated'));
    }

    /**
     * Updates the Agent Password
     *
     * @param ChangePasswordRequest $request
     * @return Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $credentials = $request->only('password');
        if (!Hash::check($request->old_password, $request->user()->password)) {
            return redirect(route('admin.profileSettings') . '#changePassword')
                ->withErrors([
                    'old_password' => 'Incorrect Old Password',
                ]);
        }

        $credentials['password'] = Hash::make($request['password']);
        $admin                   = $request->user('admin');
        $admin->update($credentials);

        return redirect(route('admin.profileSettings') . '#changePassword')
            ->with('success', __('crud.inputs.password') . ' ' . __('crud.general.updated'));
    }

    public function getAdminCredits(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        $result =  DB::select(
            "SELECT 
                sum(amount) as total,
                DATE_FORMAT(created_at, '%Y-%m') as duration
            FROM
                admin_wallets
            GROUP BY duration
            ORDER BY duration asc
            "
        );
        return response()->json($result);
    }

    public function getUserRequestStatus(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        $now   = Carbon::now()->toDateTimeString();
        $start = Carbon::now()->startOfYear();

        $result['CANCELLED'] =  DB::select(
            "SELECT 
                COUNT(allCancel.id) as cancelled_requests,
                allCancel.duration as duration from(
                    Select id, DATE_FORMAT(created_at, '%Y-%m') as duration 
                    FROM user_requests 
                    where status = 'CANCELLED' AND
                    created_at BETWEEN '$start' AND '$now'
                ) as allCancel
            GROUP BY allCancel.duration
            ORDER BY allCancel.duration asc"
        );

        $result['COMPLETED'] =  DB::select(
            "SELECT 
                    COUNT(allDone.id) as completed_requests,
                    allDone.duration as duration from(
                        Select id, DATE_FORMAT(created_at, '%Y-%m') as duration 
                        FROM user_requests 
                        where status = 'COMPLETED' AND
                        created_at BETWEEN '$start' AND '$now'
                    ) as allDone
                GROUP BY allDone.duration
                ORDER BY allDone.duration asc"
        );

        return response()->json($result);
    }

    // If anything is not broken remove this function. Older version of showing requests.
    // public function userRequest(UserRequest $userRequest)
    // {
    //     return view('admin.user_request_template', compact('userRequest'));
    // }
}
