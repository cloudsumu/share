<?php

namespace App\Http\Controllers\Api\User;

// use DB;
// use Log;
// use Route;
// use Notification;
// use App\Models\Time;
use Auth;
// use App\Notifications\WebPush;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Admin;
use GuzzleHttp\Client;
use App\Models\PeakHour;
use App\Models\Provider;
use App\Models\GeoFencing;
use App\Models\PaymentLog;
use App\Models\ServiceType;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\RequestFilter;
use App\Services\ServiceTypes;
use App\Models\ProviderService;
use App\Models\ServicePeakHour;
use App\Models\UserRequestRating;
use App\Models\UserRequestDispute;
use App\Models\UserRequestPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserRequestLostItems;
use App\Models\CancelReason as Reason;
use App\Models\ServiceRentalHourPackage;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\Api\Provider\TripController;
use App\Http\Controllers\TransactionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestApiController extends Controller
{
    /**
     * Verifica distância entre 2 pontos
     *
     * @return \Illuminate\Http\Response
     */
    public function getLiveDirection(Request $request)
    {

        $location   = null;
        //try {
        $s_latitude  = $request->s_latitude;
        $s_longitude = $request->s_longitude;
        $d_latitude  = empty($request->d_latitude) ? $request->s_latitude : $request->d_latitude;
        $d_longitude = empty($request->d_longitude) ? $request->s_longitude : $request->d_longitude;
        $location = directionGoogle($s_latitude,$s_longitude,$d_latitude,$d_longitude);
        
        return $location;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function trip_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        try {
            $UserRequests = UserRequest::UserTripDetails(auth()->user()->id, $request->request_id)->get();

            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=1' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x191919|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }

                $UserRequests[0]->dispute = UserRequestDispute::
                //where('dispute_type', 'user')->
                where('request_id', $request->request_id)->where('user_id', auth()->user()->id)->first();

                $UserRequests[0]->lostitem = UserRequestLostItems::where('request_id', $request->request_id)->where('user_id', auth()->user()->id)->first();

                $UserRequests[0]->contact_number = config('constants.contact_number', '');
                $UserRequests[0]->contact_email  = config('constants.contact_email', '');
            }
            return $UserRequests;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return Collection
     */
    public function services(Request $request)
    {
        if ($serviceList = ServiceType::with('rental_hour_package')->get()) {
            return $serviceList;
        } else {
            return response()->json(['error' => trans('api.services_not_found')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_request(Request $request)
    {
        if ($request->ajax()) {
            $this->validate($request, [
                's_latitude'   => 'required|numeric',
                's_longitude'  => 'numeric',
                'd_latitude'   => 'numeric|numeric',
                'd_longitude'  => 'numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                //'promocode_id' => 'sometimes|exists:promocodes,id',
                'distance'             => 'required|numeric',
                'use_wallet'           => 'numeric',
                'estimated_fare'       => 'sometimes|numeric',
                'service_required'     => 'required|in:none,rental,outstation',
                //TODO ALLAN - Alterações débito na máquina e voucher
                'payment_mode' => 'required|in:BRAINTREE,CASH,DEBIT_MACHINE,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . auth()->user()->id],
            ]);
        } else {
            $this->validate($request, [
                's_latitude'   => 'required|numeric',
                's_longitude'  => 'required|numeric',
                'd_latitude'   => 'required|numeric',
                'd_longitude'  => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                //'promocode_id' => 'sometimes|exists:promocodes,id',
                'distance'   => 'required|numeric',
                'use_wallet' => 'numeric',

                //TODO ALLAN - Alterações débito na máquina e voucher
                'payment_mode' => 'required|in:BRAINTREE,CASH,CARD,DEBIT_MACHINE,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . auth()->user()->id],
            ]);
        }

        $check =$this->poly_check_request((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));

        if ($check == 'no') {
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.no_service')], 422);
            } else {
                return redirect('dashboard')->with('flash_error', trans('api.ride.no_service'));
            }
        }
        //\Log::alert($request->all());
        $geo_check = $this->poly_check_new((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));
        //\Log::alert($geo_check);
        $ActiveRequests = UserRequest::PendingRequest(auth()->user()->id)->count();

        if ($ActiveRequests > 0) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.ride.request_inprogress')], 422);
            } else {
                return redirect('dashboard')->with('flash_error', trans('api.ride.request_inprogress'));
            }
        }

        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time  = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling =UserRequest::where('status', 'SCHEDULED')
                ->where('user_id', auth()->user()->id)
                ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                ->count();

            if ($CheckScheduling > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 422);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
                }
            }
        }

        $distance = config('constants.provider_search_radius', '10');

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
        // dd($Providers);
        //Log::info($Providers);
        // List Providers who are currently busy and add them to the filter list.

        if ($request->service_required != 'outstation') {
            if (count($Providers) == 0) {
                if ($request->ajax()) {
                    // Push Notification to User
                    return response()->json(['error' => trans('api.ride.no_providers_found')], 422);
                } else {
                    return back()->with('flash_success', trans('api.ride.no_providers_found'));
                }
            }
        }
        try {
            if ($request->service_required == 'rental') {
                $details = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request->s_latitude . ',' . $request->s_longitude . '&destinations=' . $request->s_latitude . ',' . $request->s_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            } else {
                $details = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request->s_latitude . ',' . $request->s_longitude . '&destinations=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            }

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = (!empty($details['routes'])) ? $details['routes'][0]['overview_polyline']['points'] : '';

            $UserRequest             = new UserRequest();
            $UserRequest->booking_id = generate_booking_id();
            if ($request->has('braintree_nonce') && $request->braintree_nonce != null) {
                $UserRequest->braintree_nonce = $request->braintree_nonce;
            }

            $UserRequest->user_id = auth()->user()->id;

            if ((config('constants.manual_request', 0) == 0) && (config('constants.broadcast_request', 0) == 0)) {
                $UserRequest->current_provider_id = $Providers[0]->id;
            } else {
                $UserRequest->current_provider_id = 0;
            }

            
            //Calcula valor estimado da tarifa
            if (!$request->has('estimated_fare')) {
                try {
                    $response = new ServiceTypes();
    
                    $responsedata = $response->calculateFare($request->all(), 1);
    
                    if ($request->service_required != 'outstation' && $request->service_required != 'rental') {
                        $UserRequest->estimated_fare = $request->estimated_fare ? $request->estimated_fare : $responsedata['data']['estimated_fare'];
                    } else {
                        $UserRequest->estimated_fare =  $request->estimated_fare;
                    }
                } catch (Exception $e) {
                    return response()->json(['error' => $e->getMessage().$e], 500);
                }
            }else{
                $UserRequest->estimated_fare =  $request->estimated_fare;
            }
            

            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->rental_hours    = $request->rental_hours;
            $UserRequest->payment_mode    = $request->payment_mode;
            $UserRequest->promocode_id    = $request->promocode_id ?: 0;

            $UserRequest->status = 'SEARCHING';

            $UserRequest->service_required = $request->service_required;

            $UserRequest->s_address = $request->s_address ?: '';
            $UserRequest->d_address = $request->d_address ?: '';

            $UserRequest->s_latitude  = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            if ($request->service_required == 'rental') {
                $UserRequest->d_latitude  = $request->s_latitude;
                $UserRequest->d_longitude = $request->s_longitude;
            } else {
                $UserRequest->d_latitude  = $request->d_latitude ? $request->d_latitude : $request->s_latitude;
                $UserRequest->d_longitude = $request->d_longitude ? $request->d_longitude : $request->s_longitude;
            }

            if ($geo_check != 0) {
                $UserRequest->geo_fencing_id = $geo_check;
            }
            if ($request->service_required == 'outstation') {
                //if ($request->has('leave')) {
                $UserRequest->status       = 'SCHEDULED';
                $UserRequest->schedule_at  = date('Y-m-d H:i:s', strtotime("$request->leave"));
                $UserRequest->is_scheduled = 'YES';

                $UserRequest->out_leave  = $request->leave;
                $UserRequest->out_return = $request->return;
                $UserRequest->day        = $request->day;
                //}
            }

            if ($request->d_latitude == null && $request->d_longitude == null) {
                $UserRequest->is_drop_location = 0;
            }

            $UserRequest->destination_log = json_encode([['latitude' => $UserRequest->d_latitude, 'longitude' => $request->d_longitude, 'address' => $request->d_address]]);
            $UserRequest->distance        = $request->distance;
            $UserRequest->unit            = config('constants.distance', 'Kms');

            if (auth()->user()->wallet_balance > 0) {
                $UserRequest->use_wallet = $request->use_wallet ?: 0;
            }

            if (config('constants.track_distance', 0) == 1) {
                $UserRequest->is_track = 'YES';
            }

            $UserRequest->otp = mt_rand(1000, 9999);

            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key   = $route_key;

            if (count($Providers) <= config('constants.surge_trigger', 0) && count($Providers) > 0) {
                $UserRequest->surge = 1;
            }

            if ($request->has('schedule_date') && $request->has('schedule_time')) {
                $UserRequest->status       = 'SCHEDULED';
                $UserRequest->schedule_at  = date('Y-m-d H:i:s', strtotime("$request->schedule_date $request->schedule_time"));
                $UserRequest->is_scheduled = 'YES';
            }
            $UserRequest->save();

            if ($UserRequest->status != 'SCHEDULED') {
                if ((config('constants.manual_request', 0) == 0) && (config('constants.broadcast_request', 0) == 0)) {
                    Log::info('New Request id : ' . $UserRequest->id . ' Assigned to provider : ' . $UserRequest->current_provider_id);
                    (new SendPushNotification())->IncomingRequest($Providers[0]->id, $UserRequest->id);
                }
            }

            $UserRequest->save();

            if ((config('constants.manual_request', 0) == 1)) {
                $admins = Admin::select('id')->get();

                foreach ($admins as $admin_id) {
                    $admin = Admin::find($admin_id->id);
                    //$admin->notify(new WebPush('Notifications', trans('api.push.incoming_request'), route('admin.dispatcher.index')));
                }
            }

            // update payment mode
            User::where('id', auth()->user()->id)->update(['payment_mode' => $request->payment_mode]);

            if ($request->has('card_id')) {
                Card::where('user_id', auth()->user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }
            if ($UserRequest->status != 'SCHEDULED') {
                if (config('constants.manual_request', 0) == 0) {
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
            }
            if ($request->ajax()) {
                return response()->json([
                    'message'          => ($UserRequest->status == 'SCHEDULED') ? 'Order schedule created!' : 'New order created!',
                    'request_id'       => $UserRequest->id,
                    'current_provider' => $UserRequest->current_provider_id,
                ]);
            } else {
                if ($UserRequest->status == 'SCHEDULED') {
                    $request->session()->flash('flash_success', 'Your ride is scheduled!');
                }
                return redirect('dashboard');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the nearby providers.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_providers(Request $request)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'service'   => 'numeric|exists:service_types,id',
        ]);

        // TODO - Update user city by latitude and longitude

        User::where('id', auth()->user()->id)->update(['latitude' => $request->latitude, 'longitude' => $request->longitude]);

        try {
            //Alterado por Allan
            $distance  = config('constants.provider_search_radius', '10');
            $latitude  = $request->latitude;
            $longitude = $request->longitude;

            if ($request->has('service')) {
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
                    ->get();
            } else {
                $ActiveProviders = ProviderService::where('status', 'active')
                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
                    ->get();
            }

            return $Providers;
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel_request(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,user_id,' . auth()->user()->id,
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if ($UserRequest->status == 'CANCELLED') {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 422);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_cancelled'));
                }
            }

            if (in_array($UserRequest->status, ['SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED'])) {
                if ($UserRequest->status != 'SEARCHING') {
                    $this->validate($request, [
                        'cancel_reason' => 'max:255',
                    ]);
                }

                $UserRequest->status = 'CANCELLED';

                if ($request->cancel_reason == 'ot') {
                    $UserRequest->cancel_reason = $request->cancel_reason_opt;
                } else {
                    $UserRequest->cancel_reason = $request->cancel_reason;
                }

                $UserRequest->cancelled_by = 'USER';
                // 'cancel_charge'
                if ($UserRequest->cancel_charge == null && config('constants.cancel_charge') != 0  && $UserRequest->status != 'SEARCHING') {
                    (new TransactionResource())->userCreditDebit(config('constants.cancel_charge'), $UserRequest, 3);
                    $UserRequest->cancel_charge = config('constants.cancel_charge');
                    }else{
                        $UserRequest->cancel_charge = 0;
                    }
                $UserRequest->save();

                

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if ($UserRequest->status != 'SCHEDULED') {
                    if ($UserRequest->provider_id != 0) {
                        ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
                    }
                }

                // Send Push Notification to User
                (new SendPushNotification())->UserCancellRide($UserRequest);

                if ($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]);
                } else {
                    return redirect('dashboard')->with('flash_success', trans('api.ride.ride_cancelled'));
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 422);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_onride'));
                }
            }
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function extend_trip(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,user_id,' . auth()->user()->id,
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
            'address'    => 'required',
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $UserRequest->s_latitude . ',' . $UserRequest->s_longitude . '&destination=' . $request->latitude . ',' . $request->longitude . '&mode=driving&key=' . config('constants.server_map_key');

            $json = curl($details);

            $details = json_decode($json, true);
            \Log::alert('direction hit by ' . $UserRequest);
            \Log::alert($details);
            $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

            $destination_log   = json_decode($UserRequest->destination_log);
            $destination_log[] = ['latitude' => $request->latitude, 'longitude' => $request->longitude, 'address' => $request->address];

            //Seta nova distância
            $locationarr           = ['s_latitude' => $UserRequest->s_latitude, 's_longitude' => $UserRequest->s_longitude, 'd_latitude' => $request->latitude, 'd_longitude' => $request->longitude];
            $UserRequest->distance = $this->getLocationDistance($locationarr);

            $UserRequest->d_latitude      = $request->latitude;
            $UserRequest->d_longitude     = $request->longitude;
            $UserRequest->d_address       = $request->address;
            $UserRequest->route_key       = $route_key;
            $UserRequest->destination_log = json_encode($destination_log);

            $UserRequest->save();

            $message = trans('api.destination_changed');

            (new SendPushNotification())->sendPushToProvider($UserRequest->provider_id, $message);

            (new SendPushNotification())->sendPushToUser($UserRequest->user_id, $message);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Verifica distância entre 2 pontos
     *
     * @return \Illuminate\Http\Response
     */
    public function getLocationDistance($locationarr)
    {
        $fn_response=['data'=>null, 'errors'=>null];

        try {
            $s_latitude  = $locationarr['s_latitude'];
            $s_longitude = $locationarr['s_longitude'];
            $d_latitude  = empty($locationarr['d_latitude']) ? $locationarr['s_latitude'] : $locationarr['d_latitude'];
            $d_longitude = empty($locationarr['d_longitude']) ? $locationarr['s_longitude'] : $locationarr['d_longitude'];
            $apiurl      = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&units=imperial&key=' . config('constants.server_map_key');
            $client      = new Client();
            $location    = $client->get($apiurl);
            $location    = json_decode($location->getBody(), true);

            if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                throw new Exception('Out of service area', 1);
            }
            $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'] ?? 0;
            $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'] ?? '1 min';
            $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'] ?? 0;
        } catch (Exception $e) {
            $fn_response['errors']=trans('user.maperror');
        }
        return round($fn_response['meter'] / 1000, 1);//RETORNA QUILÔMETROS
    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */
    public function request_status_check()
    {
        try {
            $check_status = ['CANCELLED', 'SCHEDULED'];

            $UserRequests = UserRequest::UserRequestStatusCheck(auth()->user()->id, $check_status)
                ->get()
                ->toArray();

            $search_status      = ['SEARCHING', 'SCHEDULED'];
            $UserRequestsFilter = UserRequest::UserRequestAssignProvider(auth()->user()->id, $search_status)->get();

            $package_time_all = ServiceRentalHourPackage::where('service_type_id', 2)->orderBy('hour')->get();

            if (!empty($UserRequests)) {
                //$UserRequests[0]['rent_plan'] = ServiceRentalHourPackage::where('id', $UserRequests[0][rental_hours])->first();
                $UserRequests[0]['ride_otp']  = (int)config('constants.ride_otp', 0);
                $UserRequests[0]['ride_toll'] = (int)config('constants.ride_toll', 0);
                $UserRequests[0]['cancel_charge'] = (int)config('constants.cancel_charge', 0);
                $UserRequests[0]['reasons']   = Reason::where('for', 'user')->get();
            }

            $Timeout = config('constants.provider_accept_timeout', 180);
            $type    = config('constants.broadcast_request', 0);

            if (!empty($UserRequestsFilter)) {
                for ($i = 0; $i < sizeof($UserRequestsFilter); $i++) {
                    if ($type == 1) {
                        $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->created_at));
                        if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                            UserRequest::where('id', $UserRequestsFilter[$i]->id)->update(['status' => 'CANCELLED']);
                            // No longer need request specific rows from RequestMeta
                            RequestFilter::where('request_id', $UserRequestsFilter[$i]->id)->delete();
                        } elseif ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
                            break;
                        }
                    } else {
                        $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                        if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                            $Providertrip = new TripController();
                            $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                        } elseif ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
                            break;
                        }
                    }
                }
            }

            if (empty($UserRequests)) {
                $cancelled_request = UserRequest::where('user_requests.user_id', auth()->user()->id)
                    ->where('user_requests.user_rated', 0)
                    ->where('user_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
                    ->where('updated_at', '>=', \Carbon\Carbon::now()->subSeconds(5))
                    ->first();

                if ($cancelled_request != null) {
                    \Session::flash('flash_error', $cancelled_request->cancel_reason);
                }
            }

            return response()->json([
                'data'   => $UserRequests,
                'sos'    => config('constants.sos_number', '190'),
                'cash'   => (int) config('constants.cash_payment'),
                'online' => (int)config('constants.online_payment'),
                //TODO ALLAN - Alterações Debit na máquina e voucher
                'debit_machine' => (int)config('constants.debit_machine'),
                'voucher'       => (int)config('constants.voucher'),

                'card'                   => (int)config('constants.stripe_payment'),
                'currency'               => config('constants.currency', '$'),
                'payumoney'              => (int)config('constants.payumoney'),
                'paypal'                 => (int)config('constants.paypal'),
                'paypal_adaptive'        => (int)config('constants.paypal_adaptive'),
                'braintree'              => (int)config('constants.braintree'),
                'paytm'                  => (int)config('constants.paytm'),
                'stripe_secret_key'      => config('constants.stripe_secret_key'),
                'stripe_publishable_key' => config('constants.stripe_publishable_key'),
                'stripe_currency'        => config('constants.stripe_currency'),
                'payumoney_environment'  => config('constants.payumoney_environment'),
                'payumoney_key'          => config('constants.payumoney_key'),
                'payumoney_salt'         => config('constants.payumoney_salt'),
                'payumoney_auth'         => config('constants.payumoney_auth'),
                'paypal_environment'     => config('constants.paypal_environment'),
                'paypal_currency'        => config('constants.paypal_currency'),
                'paypal_client_id'       => config('constants.paypal_client_id'),
                'paypal_client_secret'   => config('constants.paypal_client_secret'),
                'braintree_environment'  => config('constants.braintree_environment'),
                'braintree_merchant_id'  => config('constants.braintree_merchant_id'),
                'braintree_public_key'   => config('constants.braintree_public_key'),
                'braintree_private_key'  => config('constants.braintree_private_key'),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate_provider(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . auth()->user()->id,
            'rating'     => 'required|integer|in:1,2,3,4,5',
            'comment'    => 'max:255',
        ]);

        $UserRequests = UserRequest::where('id', $request->request_id)
            ->where('status', 'COMPLETED')
            ->where('paid', 0)
            ->first();

        if ($UserRequests) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.user.not_paid')], 422);
            } else {
                return back()->with('flash_error', trans('api.user.not_paid'));
            }
        }

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id'  => $UserRequest->provider_id,
                    'user_id'      => $UserRequest->user_id,
                    'request_id'   => $UserRequest->id,
                    'user_rating'  => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'user_rating'  => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            // Send Push Notification to Provider
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.provider_rated')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.provider_rated'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function modifiy_request(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . auth()->user()->id,
            'latitude'   => 'sometimes|nullable|numeric',
            'longitude'  => 'sometimes|nullable|numeric',
            'address'    => 'sometimes|nullable',

            //TODO ALLAN - Alterações débito na máquina e voucher
            'payment_mode' => 'sometimes|nullable|in:BRAINTREE,CASH,CARD,DEBIT_MACHINE,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

            'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . auth()->user()->id],
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if (!empty($request->latitude) && !empty($request->longitude)) {
                $UserRequest->d_latitude  = $request->latitude ?: $UserRequest->d_latitude;
                $UserRequest->d_longitude = $request->longitude ?: $UserRequest->d_longitude;
                $UserRequest->d_address   = $request->address ?: $UserRequest->d_address;
            }

            if ($request->has('braintree_nonce') && $request->braintree_nonce != null) {
                $UserRequest->braintree_nonce = $request->braintree_nonce;
            }

            if (!empty($request->payment_mode)) {
                $UserRequest->payment_mode = $request->payment_mode;
                if ($request->payment_mode == 'CARD' && $UserRequest->status == 'DROPPED') {
                    $UserRequest->status = 'COMPLETED';
                }
            }

            $UserRequest->save();

            if ($request->has('card_id')) {
                Card::where('user_id', auth()->user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }

            // Send Push Notification to Provider
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.request_modify_location')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.request_modify_location'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function estimated_fare_old(Request $request)
    {
        $this->validate($request, [
            's_latitude'  => 'required|numeric',
            's_longitude' => 'required|numeric',
            // 'd_latitude' => 'required|numeric',
            //'d_longitude' => 'required|numeric',
            'service_type'     => 'required|numeric|exists:service_types,id',
            'service_required' => 'in:none,rental,outstation',
        ]);

        try {
            $non_geo_price = 0;

            if ($request->service_required == 'rental') {
                $details = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request->s_latitude . ',' . $request->s_longitude . '&destinations=' . $request->s_latitude . ',' . $request->s_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            } else {
                $details = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request->s_latitude . ',' . $request->s_longitude . '&destinations=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            }

            $json = curl($details);

            $details = json_decode($json, true);

            $package_hour=0;
            $leave       = 0;
            $return      = 0;
            $day         = 0;
            $time_charge = 0;
            if (isset($details['rows'][0]['elements']) && isset($details['rows'][0]['elements'][0]) && isset($details['rows'][0]['elements'][0]['distance'])) {
                $meter   = $details['rows'][0]['elements'][0]['distance']['value'];
                $time    = $details['rows'][0]['elements'][0]['duration']['text'];
                $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

                //$kilometer = number_format(($meter / 1000), 1);
                $kilometer = ($meter / 1000);
                $minutes   = round($seconds / 60);
                //return $kilometer;
                $rental_hour = round($minutes / 60);
                $rental      = ceil($rental_hour);

                if ($request->rental_hours != null) {
                    $package = ServiceRentalHourPackage::where('id', $request->rental_hours)->first();
                    if ($package) {
                        $package_hour = $package->hour;
                        //dd($package);
                        if ($rental_hour > $package->hour) {
                            $rental = ceil($rental_hour);
                        } else {
                            $rental = ceil($package->hour);
                        }
                    } else {
                        $rental = ceil($rental_hour);
                    }
                }

                $fixed_price_only = ServiceType::findOrFail($request->service_type);
                //return $request->all;
                $geo_fencing=$this->poly_check_new((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));
                //return $geo_fencing;
                if ($geo_fencing) {
                    $service_type_id          = $request->service_type;
                    $geo_fencing_service_type = GeoFencing::with(
                        ['service_geo_fencing' => function ($query) use ($service_type_id) {
                        $query->where('service_type_id', $service_type_id);
                        $query->where('status', 1);
                    }]
                    )->whereid($geo_fencing)->first();

                    $service_type = $geo_fencing_service_type->service_geo_fencing;
                    if (empty($service_type)) {
                        return response()->json(['error' => trans('api.ride.no_service_in_area')], 500);
                    }
                    ////////---------------Peak Time Calculation--------------------//////////

                    //// peak Time Variable

                    $peak_time     = 0;
                    $non_peak_time = 0;

                    //// peak Time Variable
                    $current_date = Carbon::now();

                    $start_time = date('h:i A', strtotime($current_date));

                    $time_check_start = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();
                    // dd($time_check_start);
                    $time_charge= $minutes * ($service_type->minute ?? 0);

                    if (!empty($time_check_start)) {
                        $timeprice = ServicePeakHour::where('service_type_id', $request->service_type)->where('peak_hours_id', $time_check_start->id)->first();

                        if ($timeprice) {
                            $time_charge = $minutes * $timeprice->peak_price;
                        }
                    }

                    $total_peak_minute_and_non_peak_charge = $time_charge;

                //////// -----------------Peak Time Calculation ------------ /////////
                } else {
                    //return response()->json(['error' => trans('api.ride.no_service_in_area') ], 500);
                    $fixed_price_only = ServiceType::findOrFail($request->service_type);
                }

                $tax_percentage        = config('constants.tax_percentage', 0);
                $commission_percentage = config('constants.commission_percentage', 0);

                $current_time = Carbon::now();

                $start_time = date('h:i A', strtotime($current_time));

                $time_check_start = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();

                $travel_time      = $minutes;
                //return $service_type;

                //if ($geo_fencing) {
                //    $price = $service_type->fixed;
                //} else {
                $price = $fixed_price_only->fixed;
                //}

                //return $service_type;
                $hour = $service_type->hour;
                if ($fixed_price_only->calculator == 'MIN') {
                    $price += $service_type->minute * $minutes;
                } elseif ($fixed_price_only->calculator == 'HOUR') {
                    $price += $service_type->minute * 60;
                } elseif ($fixed_price_only->calculator == 'DISTANCE') {
                    $kilmin =$kilometer - $service_type->distance > 0 ? ($kilometer - $service_type->distance) : 0;
                    $price += ($kilmin * $service_type->price);
                } elseif ($fixed_price_only->calculator == 'DISTANCEMIN') {
                    $price += ((($kilometer - $service_type->distance > 0 ? ($kilometer - $service_type->distance) : 0) * $service_type->price)) + ($service_type->minute * $minutes);
                } elseif ($fixed_price_only->calculator == 'DISTANCEHOUR') {
                    $kilmin = $kilometer - $service_type->distance;
                    $price += ($kilmin * $service_type->price) + ($rental * $hour);
                } else {
                    $kilmin = $kilometer - $service_type->distance;
                    $price += ($kilmin * $service_type->price);
                }
                //return $service_type;
                if ($request->service_required == 'none') {
                    if ($kilometer >= $service_type->city_limits) {
                        return response()->json(['error' => 'Please book Outstation ride distance greater than ' . $service_type->city_limits . 'Km.'], 500);
                    }
                }
                //return $fixed_price_only;
                if ($request->service_required == 'rental') {
                    $package = ServiceRentalHourPackage::where('id', $request->rental_hours)->first();
                    $price   = $package->price;
                } elseif ($request->service_required == 'outstation') {
                    $begin = new DateTime($request->leave);
                    $end   = new DateTime($request->return);

                    $total_days =  $end->diff($begin)->format('%a') + 1;

                    //dd($total_days);
                    $leave  = $request->leave;
                    $return = $request->return;
                    $day    = $request->day;

                    if ($day == 'round') {
                        $kilometer          =floatval($kilometer);
                        $outstation_base_km =floatval(config('constants.outstation_base_km', '0'));
                        if ($kilometer < $outstation_base_km) {
                            $outstation_base_km = config('constants.outstation_base_km', '0');
                            $kilometer          = $outstation_base_km * $total_days;
                            $price              = (($kilometer * $fixed_price_only->roundtrip_km) + ($fixed_price_only->outstation_driver * $total_days));
                        } else {
                            $kilometer = $kilometer * $total_days;
                            $price     = (($kilometer * $fixed_price_only->roundtrip_km) + ($fixed_price_only->outstation_driver * $total_days));
                        }
                    } else {
                        $kilometer          =floatval($kilometer);
                        $outstation_base_km =floatval(config('constants.outstation_base_km', '0'));
                        if ($kilometer < $outstation_base_km) {
                            $outstation_base_km = config('constants.outstation_base_km', '0');
                            $kilometer          = $outstation_base_km * $total_days;
                            $price              = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));
                        } else {
                            $kilometer = $kilometer * $total_days;
                            $price     = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));
                        }
                    }
                }

                if (!empty($time_check_start)) {
                    $timeprice = ServicePeakHour::where('peak_hours_id', $time_check_start->id)->first();

                    if (!empty($timeprice)) {
                        $price += $timeprice->peak_price * $minutes;
                    }
                }

                $tax_price       = ($tax_percentage / 100) * $price;
                $total           = $price + $tax_price;

                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id');

                $distance  = config('constants.provider_search_radius', '10');

                $latitude  = $request->s_latitude;
                $longitude = $request->s_longitude;

                $Providers = Provider::whereIn('id', $ActiveProviders)
            ->where('status', 'approved')
            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->get();

                $surge = 0;

                if (count($Providers) <= config('constants.surge_trigger', 0) && count($Providers) > 0) {
                    $surge_price = (config('constants.surge_percentage', 0) / 100) * $total;
                    $total += $surge_price;
                    $surge = 1;
                }

                $city_limits     = 0;
                $service_type_id =$request->service_type;
                $geo_fencing_id  =$this->poly_check_new((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));

                if ($geo_fencing_id != 0) {
                    $geo_fencing_service_type = GeoFencing::with(
                        ['service_geo_fencing' => function ($query) use ($service_type_id) {
                        $query->where('service_type_id', $service_type_id);
                        $query->where('status', 1);
                    }]
                    )->whereid($geo_fencing_id)->first();

                    $D_geo_fencing_id=$this->poly_check_new((round($request->d_latitude, 6)), (round($request->d_longitude, 6)));

                    // if(!empty($geo_fencing_service_type->service_geo_fencing->city_limits) && $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
                    if ($D_geo_fencing_id == 0) {
                        if ($request->service_required == 'outstation') {
                            $city_limits = 1;
                        } elseif ($request->service_required == 'none') {
                            $city_limits = 1;
                        }
                    } else {
                        if ($request->service_required == 'outstation') {
                            $kilometer          =floatval($kilometer);
                            $outstation_base_km =floatval(config('constants.outstation_base_km', '0'));
                            if ($kilometer < $outstation_base_km) {
                                $city_limits = 1;
                            }
                        }
                    }

                    $check = $this->poly_check_request((round($request->d_latitude, 6)), (round($request->d_longitude, 6)));

                    if ($check == 'no') {
                        $non_geo_price = $geo_fencing_service_type->service_geo_fencing->non_geo_price;
                    }
                }

                $rental_hour_package = ServiceRentalHourPackage::whereservice_type_id($request->service_type)->get();

                if ($request->rental_hours) {
                    $rental_package = ServiceRentalHourPackage::findOrFail($request->rental_hours);
                } else {
                    $rental_package = '';
                }

                if ($request->service_required == 'rental') {
                    $total = $rental_package->price;
                }

                /*
                * Reported by Jeya, previously it was hardcoded. we have changed as based on surge percentage.
                */

                $surge_percentage = 1 + (config('constants.surge_percentage') / 100) . 'X';
                //if (!empty($service_type)) {
                //    $fixed_price_only = $service_type;
                //}
                //$request->session()->put('estimated_fare', round($total, 2));
                //if($total < $fixed_price_only->fixed)
                //    $total += $fixed_price_only->fixed;
                //return $fixed_price_only->fixed;
                return response()->json([
                    'estimated_fare'      => round($total, 2),
                    'distance'            => $kilometer,
                    'minute'              => $minutes,
                    'time'                => $time,
                    'surge'               => $surge,
                    'surge_value'         => $surge_percentage,
                    'tax_price'           => $tax_price,
                    'base_price'          => $fixed_price_only->fixed,
                    'service_type'        => $fixed_price_only->id,
                    'wallet_balance'      => Auth::user()->wallet_balance,
                    'city_limits'         => $city_limits,
                    'service_required'    => $request->service_required,
                    'rental_hours'        => $package_hour,
                    'leave'               => $leave,
                    'return'              => $return,
                    'day'                 => $day,
                    'limit_message'       => config('constants.limit_message', 'text here'),
                    'non_geo_price'       => $non_geo_price,
                    'rental_hour_package' => $rental_hour_package,
                    'time_package'        => $rental_package,
                    'rental_package'      => $rental_package,
                ]);
            } else {
                //'No Service Found.'.
                return response()->json(['error' => $details], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function estimated_fare(Request $request)
    {
        $this->validate($request, [
            's_latitude'  => 'required|numeric',
            's_longitude' => 'required|numeric',
            // 'd_latitude' => 'required|numeric',
            //'d_longitude' => 'required|numeric',
            'service_type'     => 'required|numeric|exists:service_types,id',
            'service_required' => 'required|in:none,rental,outstation',
        ]);

        try {
            $response     = new ServiceTypes();
            $responsedata = $response->calculateFare($request->all());

            if (!empty($responsedata['errors'])) {
                throw new Exception($responsedata['errors']);
            } else {
                return response()->json($responsedata['data']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fare_without_auth(Request $request)
    {
        $this->validate($request, [
            's_latitude'  => 'required|numeric',
            's_longitude' => 'required|numeric',
            // 'd_latitude' => 'required|numeric',
            //'d_longitude' => 'required|numeric',
            'service_type'     => 'required|numeric|exists:service_types,id',
            'service_required' => 'sometimes|in:none,rental,outstation',
        ]);

        try {
            $response     = new ServiceTypes();
            $responsedata = $response->calculateFare($request->all());

            if (!empty($responsedata['errors'])) {
                throw new Exception($responsedata['errors']);
            } else {
                return response()->json($responsedata['data']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function chatPush(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric',
            'message' => 'required',
        ]);

        try {
            $user_id = $request->user_id;
            $message = $request->message;
            $sender  = $request->sender;

            $message = \PushNotification::Message($message, [
                'badge'  => 1,
                'sound'  => 'default',
                'custom' => ['type' => 'chat'],
            ]);

            (new SendPushNotification())->sendPushToProvider($user_id, $message);

            //(new SendPushNotification)->sendPushToUser($user_id, $message);

            return response()->json(['success' => 'true']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reasons(Request $request)
    {
        $reason = Reason::where('for', 'user')->where('status', 1)->get();

        return $reason;
    }

    public function payment_log(Request $request)
    {
        $log           = PaymentLog::where('transaction_code', $request->order)->first();
        $log->response = $request->all();
        $log->save();
        return response()->json(['message' => trans('api.payment_success')]);
    }

    public function payment_online(Request $request)
    {
        $this->validate($request, [
            'request_id'       => 'required|integer|exists:user_requests,id',
            'payment_type'     => 'in:ONLINE',
            'transaction_code' => 'sometimes',
            'tips'             => 'sometimes',
        ]);

        $UserRequest                  = UserRequest::find($request->request_id);
        $RequestPayment               = UserRequestPayment::where('request_id', $request->request_id)->first();
        $UserRequest->payment_mode    = $request->payment_type;
        $RequestPayment->card         = $RequestPayment->payable;
        $RequestPayment->payable      = 0;
        $RequestPayment->tips         = $request->tips ? $request->tips : 0;
        $RequestPayment->provider_pay = $RequestPayment->provider_pay + ($request->tips ? $request->tips : 0);
        $RequestPayment->save();
        $log                   = new PaymentLog();
        $log->user_type        = 'user';
        $log->transaction_code = $request->transaction_code ?? "";
        $log->amount           = $RequestPayment->provider_pay;
        $log->transaction_id   = $request->request_id;
        $log->payment_mode     = $request->payment_type;
        $log->user_id          = \auth()->user()->id;
        $log->save();
        $UserRequest->paid   = 1;
        $UserRequest->status = 'COMPLETED';
        $UserRequest->save();

        //for create the transaction
        (new TripController())->callTransaction($request->request_id);
        if ($request->ajax()) {
            return response()->json(['message' => trans('api.paid')]);
        } else {
            return redirect('dashboard')->with('flash_success', trans('api.paid'));
        }
    }

    public function poly_check_new($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //dd($range_data);

        $yes = $no =  [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;
        if (!empty($range_data)) {
            foreach ($range_data as $ranges) {
                $vertices_x = $vertices_y = [];

                $range_values = json_decode($ranges['ranges'], true);
                //dd($range_values);
                if ($range_values != '') {
                    foreach ($range_values as $range) {
                        $vertices_x[] = $range['lat'];

                        $vertices_y[] = $range['lng'];
                    }

                    $points_polygon = count($vertices_x);
                    //dd($points_polygon);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] = $ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
            //dd($yes[0]." ".$no[0]);
            if (count($yes) != 0) {
                return $yes[0];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function poly_check_request($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //\Log::alert($range_data);

        $yes = $no =   [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;

        if (count($range_data) != 0) {
            foreach ($range_data as $ranges) {
                if (!empty($ranges)) {
                    $vertices_x = $vertices_y = [];

                    $range_values = json_decode($ranges['ranges'], true);
                    //\Log::alert($range_values);
                    if (count($range_values) > 0) {
                        foreach ($range_values as $range) {
                            $vertices_x[] = $range['lat'];
                            $vertices_y[] = $range['lng'];
                        }
                    }

                    $points_polygon = count($vertices_x);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] =$ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
        }

        if (count($yes) != 0) {
            return 'yes';
        } else {
            return 'no';
        }
    }
}
