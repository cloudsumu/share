<?php
//TODO ALLAN - Alterações e correções no sistema de pesquisa

namespace App\Http\Controllers\Admin;

use DB;
use Log;
use Exception;
use Carbon\Carbon;
use App\Dispatcher;
use App\Models\User;
use App\Models\UserRequest;
use App\Models\RequestFilter;
use App\Helpers\Helper;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Services\ServiceTypes;
use App\Models\ProviderService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SendPushNotification;

class DispatcherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo')->only(['profile_update', 'password_update']);

        if (Auth::guard('admin')->user()) {
            $this->middleware('permission:dispatcher-panel', ['only' => ['index']]);
            $this->middleware('permission:dispatcher-panel-add', ['only' => ['store']]);
        }
    }

    /**
     * Dispatcher Panel.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::guard('admin')->user()) {
            return view('admin.dispatcher');
        } elseif (Auth::guard('dispatcher')->user()) {
            return view('admin.dispatcher');
        }
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips(Request $request)
    {
        $Trips = UserRequest::with('user', 'provider')
                    ->orderBy('id', 'desc');

        if ($request->type == 'SEARCHING') {
            $Trips = $Trips->where('status', $request->type);
        } elseif ($request->type == 'CANCELLED') {
            $Trips = $Trips->where('status', $request->type);
        } elseif ($request->type == 'ASSIGNED') {
            $Trips = $Trips->whereNotIn('status', ['SEARCHING', 'SCHEDULED', 'CANCELLED', 'COMPLETED']);
        }

        return $Trips->paginate(10);
    }

    /**
     * Display a listing of the users in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        $Users = new User();

        if ($request->has('mobile')) {
            $Users->where('mobile', 'like', $request->mobile . '%');
        }

        if ($request->has('first_name')) {
            $Users->where('first_name', 'like', $request->first_name . '%');
        }

        if ($request->has('last_name')) {
            $Users->where('last_name', 'like', $request->last_name . '%');
        }

        if ($request->has('email')) {
            $Users->where('email', 'like', $request->email . '%');
        }

        return $Users->paginate(10);
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function providers(Request $request)
    {
        $Providers = new Provider();

        if ($request->has('latitude') && $request->has('longitude')) {
            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                    ->get()
                    ->pluck('provider_id');

            $distance  = config('constants.provider_search_radius', '10');
            $latitude  = $request->latitude;
            $longitude = $request->longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->with('service', 'service.service_type')
                ->get();

            //$Providers = Provider::all();

            return $Providers;
        }

        return $Providers;
    }

    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */
    public function assign($request_id, $provider_id)
    {
        try {
            $Request  = UserRequests::findOrFail($request_id);
            $Provider = Provider::findOrFail($provider_id);

            $Request->status              = 'SEARCHING';
            $Request->current_provider_id = $Provider->id;
            $Request->assigned_at         = Carbon::now();
            $Request->save();

            ProviderService::where('provider_id', $Request->provider_id)->update(['status' =>'riding']);

            (new SendPushNotification())->IncomingRequest($Request->current_provider_id, $Request->id);

            try {
                RequestFilter::where('request_id', $Request->id)
                    ->where('provider_id', $Provider->id)
                    ->firstOrFail();
            } catch (Exception $e) {
                $Filter              = new RequestFilter();
                $Filter->request_id  = $Request->id;
                $Filter->provider_id = $Provider->id;
                $Filter->status      = 0;
                $Filter->save();
            }

            if (Auth::guard('admin')->user()) {
                return redirect()
                        ->route('admin.dispatcher.index')
                        ->with('flash_success', trans('admin.dispatcher_msgs.request_assigned'));
            } elseif (Auth::guard('dispatcher')->user()) {
                return redirect()
                        ->route('dispatcher.index')
                        ->with('flash_success', trans('admin.dispatcher_msgs.request_assigned'));
            }
        } catch (Exception $e) {
            if (Auth::guard('admin')->user()) {
                return redirect()->route('admin.dispatcher.index')->with('flash_error', trans('api.something_went_wrong'));
            } elseif (Auth::guard('dispatcher')->user()) {
                return redirect()->route('dispatcher.index')->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            's_latitude'   => 'required|numeric',
            's_longitude'  => 'required|numeric',
            'd_latitude'   => 'required|numeric',
            'd_longitude'  => 'required|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'distance'     => 'required|numeric',
        ]);

        try {
            $User = User::where('mobile', $request->mobile)->firstOrFail();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $request->email)->firstOrFail();
            } catch (Exception $e) {
                $User = User::create([
                    'first_name'   => $request->first_name,
                    'last_name'    => $request->last_name,
                    'email'        => $request->email,
                    'mobile'       => $request->mobile,
                    'password'     => bcrypt($request->mobile),
                    'payment_mode' => 'CASH',
                ]);
            }
        }

        if ($request->has('schedule_time')) {
            try {
                $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                        ->where('user_id', $User->id)
                        ->where('schedule_at', '>', strtotime($request->schedule_time . ' - 1 hour'))
                        ->where('schedule_at', '<', strtotime($request->schedule_time . ' + 1 hour'))
                        ->firstOrFail();

                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
                }
            } catch (Exception $e) {
                // Do Nothing
            }
        }

        try {
            if (config('constants.voucher', 1) == 0 && $request->payment_mode == 'VOUCHER') {
                return response()->json(['message' => 'Voucher payment disabled! Go to the payment setting to enable.']);
            } elseif (config('constants.cash', 1) == 0 && $request->payment_mode == 'CASH') {
                return response()->json(['message' => 'Voucher payment disabled! Go to the payment setting to enable.']);
            } elseif (config('constants.debit_machine', 1) == 0 && $request->payment_mode == 'DEBIT_MACHINE') {
                return response()->json(['message' => 'Machine Debit Payment disabled! Go to the payment setting to enable.']);
            }

            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                    ->get()
                    ->pluck('provider_id');

            $distance     = config('constants.provider_search_radius', '10');
            $latitude     = $request->s_latitude;
            $longitude    = $request->s_longitude;
            $service_type = $request->service_type;

            $Providers = Provider::with('service')
            ->select(DB::Raw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) AS distance"), 'id')
            ->where('status', 'approved')
            ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
            ->whereHas('service', function ($query) use ($service_type) {
                $query->where('status', 'active');
                $query->where('service_type_id', $service_type);
            })
            ->orderBy('distance', 'asc')
            ->get();

            // List Providers who are currently busy and add them to the filter list.

            if (count($Providers) == 0) {
                if ($request->ajax()) {
                    // Push Notification to User
                    return response()->json(['message' => trans('api.ride.no_providers_found')]);
                } else {
                    return back()->with('flash_success', trans('api.ride.no_providers_found'));
                }
            }

            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $request->s_latitude . ',' . $request->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.server_map_key');

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest                      = new UserRequests();
            $UserRequest->booking_id          = Helper::generate_booking_id();
            $UserRequest->user_id             = $User->id;
            $UserRequest->current_provider_id = 0;
            $UserRequest->service_type_id     = $request->service_type;
            $UserRequest->payment_mode        = 'CASH';
            $UserRequest->promocode_id        = 0;
            $UserRequest->status              = 'SEARCHING';

            //TODO ALLAN - Alterações débito na máquina e voucher
            //$UserRequest->comments = ($request->comments ? $request->comments : null);

            //Calcula valor estimado da tarifa
            try {
                $response = new ServiceTypes();

                $responsedata = $response->calculateFare($request->all(), 1);

                $UserRequest->estimated_fare = $responsedata['data']['estimated_fare'];
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $UserRequest->s_address   = $request->s_address ?: '';
            $UserRequest->s_latitude  = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_address   = $request->d_address ?: '';
            $UserRequest->d_latitude  = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->route_key   = $route_key;

            $UserRequest->distance = round($request->distance);

            if ($request->has('provider_auto_assign')) {
                $UserRequest->assigned_at = Carbon::now();
            }

            $UserRequest->use_wallet = 0;
            $UserRequest->surge      = 0;        // Surge is not necessary while adding a manual dispatch

            if ($request->has('schedule_time')) {
                $UserRequest->schedule_at = Carbon::parse($request->schedule_time);
            }

            $UserRequest->save();

            if ($request->has('provider_auto_assign')) {
//                $Providers[0]->service()->update(['status' => 'riding']);

                if ((config('constants.broadcast_request', 0) == 0)) {
                    $UserRequest->current_provider_id = $Providers[0]->id;
                } else {
                    $UserRequest->current_provider_id = 0;
                }

                $UserRequest->save();

                Log::info('New Dispatch : ' . $UserRequest->id);
                Log::info('Assigned Provider : ' . $UserRequest->current_provider_id);

                foreach ($Providers as $key => $Provider) {
                    if (config('constants.broadcast_request', 0) == 1) {
                        (new SendPushNotification())->IncomingRequest($Provider->id, $UserRequest->id);
                    }

                    $Filter = new RequestFilter();
                    // Send push notifications to the first provider
                    // incoming request push to provider

                    $Filter->request_id  = $UserRequest->id;
                    $Filter->provider_id = $Provider->id;
                    $Filter->save();
                }
            }

            if ($request->ajax()) {
                return $UserRequest;
            } else {
                return redirect('dashboard');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('dispatcher.account.profile');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request, [
            'name'   => 'required|max:255',
            'mobile' => 'required|digits_between:6,13',
        ]);

        try {
            $dispatcher           = Auth::guard('dispatcher')->user();
            $dispatcher->name     = $request->name;
            $dispatcher->mobile   = $request->mobile;
            $dispatcher->language = $request->language;
            $dispatcher->save();

            return redirect()->back()->with('flash_success', trans('admin.profile_update'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('api.something_went_wrong'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        return view('dispatcher.account.change-password');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);

        try {
            $Dispatcher = Dispatcher::find(Auth::guard('dispatcher')->user()->id);

            if (password_verify($request->old_password, $Dispatcher->password)) {
                $Dispatcher->password = bcrypt($request->password);
                $Dispatcher->save();

                return redirect()->back()->with('flash_success', trans('admin.password_update'));
            }
        } catch (Exception $e) {
            return back()->with('flash_error', trans('api.something_went_wrong'));
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id',
        ]);

        try {
            $UserRequest = UserRequests::findOrFail($request->request_id);

            if ($UserRequest->status == 'CANCELLED') {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 500);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_cancelled'));
                }
            }

            if (in_array($UserRequest->status, ['SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED'])) {
                $UserRequest->status        = 'CANCELLED';
                $UserRequest->cancel_reason = 'Cancelado pelo administrador';
                $UserRequest->cancelled_by  = 'NONE';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if ($UserRequest->status != 'SCHEDULED') {
                    if ($UserRequest->provider_id != 0) {
                        ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
                    }
                }

                // Send Push Notification to User
                (new SendPushNotification())->UserCancellRide($UserRequest);
                (new SendPushNotification())->ProviderCancellRide($UserRequest);

                if ($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]);
                } else {
                    return back()->with('flash_success', trans('api.ride.ride_cancelled'));
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 500);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_onride'));
                }
            }
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }
}
