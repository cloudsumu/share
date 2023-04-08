<?php

namespace App\Http\Controllers\Api\Provider;

use Exception;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\PeakHour;
use App\Models\Provider;
use App\Models\Promocode;
use App\Models\PaymentLog;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\RequestFilter;
use App\Models\WalletRequest;
use App\Models\PromocodeUsage;
use App\Models\ProviderWallet;
use App\Services\ServiceTypes;
use App\Models\ProviderService;
use App\Models\ServicePeakHour;
use App\Services\PaymentGateway;
use App\Models\UserRequestRating;
use App\Models\RequestWaitingTime;
use App\Models\UserRequestDispute;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CancelReason as Reason;
use App\Http\Controllers\TransactionResource;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\Resource\ReferralResource;
use App\Models\Document;
use App\Models\ServiceRentalHourPackage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $provider = $Provider->id;

            $AfterAssignProvider =
                RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas(
                    'request',
                    function ($query) use ($provider) {
                        $query->where('status', '<>', 'CANCELLED');
                        $query->where('status', '<>', 'SCHEDULED');
                        $query->where('provider_id', $provider);
                        $query->where('current_provider_id', $provider);
                    }
                );

            $BeforeAssignProvider =
                RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas(
                    'request',
                    function ($query) use ($provider) {
                        $query->where('status', '<>', 'CANCELLED');
                        $query->where('status', '<>', 'SCHEDULED');
                        $query->when(config('constants.broadcast_request') == 1, function ($q) {
                            $q->where('current_provider_id', 0);
                        });
                        $query->when(config('constants.broadcast_request') == 0, function ($q) use ($provider) {
                            $q->where('current_provider_id', $provider);
                        });
                    }
                ); 

            $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();
            
            if (!empty($request->latitude)) {
                $Provider->update([
                    'latitude'  => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                //update provider service hold status
                DB::table('provider_services')->where('provider_id', $Provider->id)->where('status', 'hold')->update(['status' => 'active']);
            }
            Log::info(' Provider : ' . $IncomingRequests);
            if (config('constants.manual_request', 0) == 0) {
                $Timeout = config('constants.provider_accept_timeout', 180);
                if (!empty($IncomingRequests)) {
                    for ($i = 0; $i < sizeof($IncomingRequests); $i++) {
                        $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                        if ($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                            if (config('constants.broadcast_request', 0) == 1) {
                                $this->assign_destroy($IncomingRequests[$i]->request->id);
                            } else {
                                $this->assign_next_provider($IncomingRequests[$i]->request->id);
                            }
                        }
                    }
                }
            }
            Log::info(' Provider : ' . $IncomingRequests);
            $Reason = Reason::where('for', 'provider')->get();

            $referral_total_count  = (new ReferralResource())->get_referral('provider', Auth::user()->id)[0]->total_count;
            $referral_total_amount = (new ReferralResource())->get_referral('provider', Auth::user()->id)[0]->total_amount;

            $total_documents = Document::count();
            if ($Provider->wallet_balance <= config('constants.minimum_negative_balance')) {
                ProviderService::where('provider_id', $Provider->id)->update(['status' => 'balance']);
                Provider::where('id', $Provider->id)->update(['status' => 'balance']);
            } else if ($Provider->active_documents() == $total_documents) {
                Provider::where('id', $Provider->id)->update(['status' => 'approved']);
                //ProviderService::where('provider_id', $Provider->id)->update(['status' => 'active']);
            } else {
                Provider::where('id', $Provider->id)->update(['status' => 'document']);
                ProviderService::where('provider_id', $Provider->id)->update(['status' => 'offline']);
            }

            $status = 'offline';
            foreach ($Provider->service as $post) {
                if ($post->status != 'offline') {
                    $status = 'active';
                }
            }
            foreach ($IncomingRequests as $key => $IncomingRequest) {
                if ($IncomingRequest->request->service_required == 'rental') {
                    $package_time = ServiceRentalHourPackage::where('id', $IncomingRequest->request->rental_hours)->first();

                    $package_time_all  = ServiceRentalHourPackage::where('service_type_id', $IncomingRequest->request->service_type_id)->orderBy('hour')->get();

                    $IncomingRequest->request->rental_package = $package_time;
                    if (
                        !empty($IncomingRequest->request->started_at) &&
                        $IncomingRequest->request->finished_at == null
                    ) {

                        $carbon_date = Carbon::parse($IncomingRequest->request->started_at ?? null);

                        $final_time = $carbon_date->addHour((int) $package_time->hour);
                        $new_pack   = [];
                        if ($final_time->lt(Carbon::now())) {
                            $UserRequest = UserRequest::find($IncomingRequest->request->id);

                            foreach ($package_time_all as $key => $value) {
                                //return  $package_time_all ;

                                if ($package_time->hour < (int) $value->hour) {
                                    $IncomingRequest->request->rental_hours = $value->id;

                                    $UserRequest->rental_hours = $value->id;
                                    $UserRequest->save();

                                    break;
                                } elseif ($package_time->hour == (int) $value->hour) {
                                    if ($request->ajax()) {
                                        $Provider = auth()->user();
                                    } else {
                                        $Provider = Auth::guard('provider')->user();
                                    }
                                    if ($UserRequest->status != 'COMPLETED') {
                                        $UserRequest->status = 'DROPPED';
                                    }
                                    $locationarr            = ['s_latitude' => $UserRequest->s_latitude, 's_longitude' => $UserRequest->s_longitude, 'd_latitude' => $Provider->latitude, 'd_longitude' => $Provider->longitude];
                                    $UserRequest->distance  = $this->getLocationDistance($locationarr);
                                    $UserRequest->d_address = getAddress($Provider->latitude, $Provider->longitude);

                                    $UserRequest->finished_at = Carbon::now();
                                    $StartedDate              = date_create($UserRequest->started_at);
                                    $FinisedDate              = Carbon::now();
                                    $TimeInterval             = date_diff($StartedDate, $FinisedDate);
                                    $MintuesTime              = $TimeInterval->i;
                                    $UserRequest->travel_time = $MintuesTime;
                                    $UserRequest->d_latitude  = $Provider->latitude;
                                    $UserRequest->d_longitude = $Provider->longitude;
                                    $UserRequest->save();
                                    $UserRequest->with('user')->findOrFail($UserRequest->id);
                                    $UserRequest->invoice = $this->invoice($UserRequest->id, ($request->toll_price != null) ? $request->toll_price : 0);
                                    (new SendPushNotification())->Dropped($UserRequest);
                                    break;
                                }
                            }
                        }
                    }
                }
            }


            $Response = [
                'account_status'   => $Provider->status,
                'service_status'   => $status,
                'requests'         => $IncomingRequests,
                'provider_details' => $Provider,
                'reasons'          => $Reason, 
                  //'waitingStatus' => (count($IncomingRequests) > 0) ? $this->waiting_status($IncomingRequests[0]->request_id) : 0,
                  //'waitingTime' => (count($IncomingRequests) > 0) ? $this->total_waiting($IncomingRequests[0]->request_id) : 0, 
                'referral_count'        => config('constants.referral_count', '0'),
                'referral_amount'       => config('constants.referral_amount', '0'),
                'ride_otp'              => (int)config('constants.ride_otp'),
                'ride_toll'             => (int)config('constants.ride_toll'),
                'cancel_charge' => (int)config('constants.cancel_charge'),
                'referral_text'         => "<p style='font-size:16px; color: #fff;'>Invite your friends<br>and earn <span color='#00E4C5'>" . config('constants.currency', '') . '' . config('constants.referral_amount', '0') . '</span> per head</p>',
                'referral_total_count'  => $referral_total_count,
                'referral_total_amount' => $referral_total_amount,
                'referral_total_text'   => "<p style='font-size:16px; color: #000;'>Referral Amount: " . $referral_total_amount . '<br>Referral Count:' . $referral_total_count . '</p>',
            ];

            if (count($IncomingRequests) > 0) {
                if (!empty($request->latitude) && !empty($request->longitude)) {
                    $this->calculate_distance($request, $IncomingRequests[0]->request_id);
                }
            }
            //\Log::alert($Response);
            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Calculate distance between two coordinates.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculate_distance($request, $id)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        try {
            if ($request->ajax()) {
                $Provider = auth()->user();
            } else {
                $Provider = auth()->guard('auth:provider')->user();
            }

            $UserRequest = UserRequest::where('status', 'PICKEDUP')
                ->where('provider_id', $Provider->id)
                ->find($id);
            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    
    public function instant_ride(Request $request)
    {
        $this->validate($request, [
            's_latitude'     => 'required|numeric',
            'd_latitude'     => 'required|numeric',
            's_address'      => 'required',
            's_longitude'    => 'numeric',
            'd_longitude'    => 'numeric',
            'd_address'      => 'required',
            'estimated_fare' => 'required',
        ]);

        /* Log::info('New Request from User: '.Auth::user()->id);
          Log::info('Request Details:', $request->all()); */

        $User = User::where('mobile', $request->mobile)->orWhere('email', $request->email)->first();

        if ($User != null) {
            $ActiveRequests = UserRequest::PendingRequest($User->id)->count();

            if ($ActiveRequests > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_inprogress')], 422);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_inprogress'));
                }
            }
        }

        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time  = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequest::where('status', 'SCHEDULED')
                ->where('user_id', Auth::user()->id)
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

        $Provider = Auth::user();

        $latitude     = $request->s_latitude;
        $longitude    = $request->s_longitude;
        $service_type = ProviderService::where('provider_id', $Provider->id)->first();
        $request->request->add(['service_type' => $service_type->service_type_id]);
        $distance = (!empty($details['routes'][0]['legs'][0]['distance']['text'])) ? str_replace(' km', '', $details['routes'][0]['legs'][0]['distance']['text']) : 0;

        try {
            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $request->s_latitude . ',' . $request->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.server_map_key');

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = $details['routes'][0]['overview_polyline']['points'] ?? null;

            $latestUser = User::orderBy('id', 'desc')->first();

            // try {
            //     $response = new ServiceTypes();

            //     $responsedata = $response->calculateFare($request->all(), 1);
            // } catch (Exception $e) {
            //     return response()->json(['error' => 'Estimate Not calculated'], 500);
            // }

            $payment_mode = 'CASH';

            if ($User == null) {
                $User = User::create([
                    'first_name'   => ($request->first_name != null) ? $request->first_name : 'Instant',
                    'last_name'    => ($request->last_name != null) ? $request->last_name : 'User',
                    'country_code' => ($request->country_code != null) ? $request->country_code : '+91',
                    'mobile'       => ($request->mobile != null) ? $request->mobile : mt_rand(1, 9999999999),
                    'email'        => ($request->email != null) ? $request->email : ($latestUser->id ?? 0 ). '_instantuser@instant.com',
                    'password'     => bcrypt('123456'),
                    'payment_mode' => $payment_mode,
                    'user_type'    => 'INSTANT',
                ]);
            }

            $UserRequest             = new UserRequest();
            $UserRequest->booking_id = generate_booking_id();

            $UserRequest->user_id             = $User->id;
            $UserRequest->provider_id         = $Provider->id;
            $UserRequest->current_provider_id = $Provider->id;
            $UserRequest->service_type_id     = $service_type->service_type_id;
            $UserRequest->rental_hours        = $request->rental_hours;
            $UserRequest->payment_mode        = $payment_mode;
            $UserRequest->promocode_id        = $request->promocode_id ?: 0;

            $UserRequest->estimated_fare = $request->estimated_fare;// $responsedata['data']['estimated_fare'] ?: 0;

            $UserRequest->status          = 'PICKEDUP';
            $UserRequest->is_instant_ride = 1;

            $UserRequest->s_address = $request->s_address ?: '';
            $UserRequest->d_address = $request->d_address ?: '';

            $UserRequest->s_latitude  = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_latitude      = $request->d_latitude;
            $UserRequest->d_longitude     = $request->d_longitude;
            $UserRequest->destination_log = json_encode([['latitude' => $UserRequest->d_latitude, 'longitude' => $request->d_longitude]]);
            $UserRequest->distance        = $distance;
            $UserRequest->unit            = config('constants.distance', 'Kms');
            $UserRequest->use_wallet      = 0;

            if (config('constants.track_distance', 0) == 1) {
                $UserRequest->is_track = 'YES';
            }

            $UserRequest->otp         = mt_rand(1000, 9999);
            $UserRequest->started_at  = Carbon::now();
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key   = $route_key;

            $UserRequest->save();

            $Filter              = new RequestFilter();
            $Filter->request_id  = $UserRequest->id;
            $Filter->provider_id = $Provider->id;
            $Filter->save();

            if ($request->ajax()) {
                $Reason = Reason::where('for', 'PROVIDER')->get();

                $referral_total_count  = (new ReferralResource())->get_referral('provider', Auth::user()->id)[0]->total_count;
                $referral_total_amount = (new ReferralResource())->get_referral('provider', Auth::user()->id)[0]->total_amount;
                $status                = 'offline';
                foreach ($Provider->service as $post) {
                    if ($post->status != 'offline') {
                        $status = 'active';
                    }
                }
                $Response = [
                    'account_status'        => $Provider->status,
                    'service_status'        => $status,
                    'requests'              => [$UserRequest],
                    'provider_details'      => $Provider,
                    'reasons'               => $Reason,
                    'waitingStatus'         => $this->waiting_status($UserRequest->request_id),
                    'waitingTime'           => $this->total_waiting($UserRequest->request_id),
                    'referral_count'        => config('constants.referral_count', '0'),
                    'referral_amount'       => config('constants.referral_amount', '0'),
                    'referral_text'         => "<p style='font-size:16px; color: #fff;'>Invite your friends<br>and earn <span color='#00E4C5'>" . config('constants.currency', '') . '' . config('constants.referral_amount', '0') . '</span> per head</p>',
                    'referral_total_count'  => $referral_total_count,
                    'referral_total_amount' => $referral_total_amount,
                    'referral_total_text'   => "<p style='font-size:16px; color: #000;'>Referral Amount: " . $referral_total_amount . '<br>Referral Count:' . $referral_total_count . '</p>',
                ];

                return $Response;
            } else {
                if ($UserRequest->status == 'SCHEDULED') {
                    $request->session()->flash('flash_success', 'Your ride is scheduled!');
                }
                return redirect('dashboard');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong').$e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }
    
    /**
     * Cancel given request.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $this->validate($request, [
            'cancel_reason' => 'max:255',
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->id);
            $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED', 'SCHEDULED'];

            if (!in_array($UserRequest->status, $Cancellable)) {
                return back()->with(['flash_error' => 'Cannot cancel request at this stage!']);
            }

            $UserRequest->status        = 'CANCELLED';
            $UserRequest->cancel_reason = $request->cancel_reason;
            $UserRequest->cancelled_by  = 'PROVIDER';
            $UserRequest->save();

            RequestFilter::where('request_id', $UserRequest->id)->delete();

            ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);

            // Send Push Notification to User
            (new SendPushNotification())->ProviderCancellRide($UserRequest);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request, $id)
    {
        $this->validate($request, [
            'rating'  => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
        ]);

        try {
            $UserRequest = UserRequest::where('id', $id)
                ->where('status', 'COMPLETED')
                ->firstOrFail();

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id'      => $UserRequest->provider_id,
                    'user_id'          => $UserRequest->user_id,
                    'request_id'       => $UserRequest->id,
                    'provider_rating'  => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'provider_rating'  => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            }

            $UserRequest->update(['provider_rated' => 1]);

            // Delete from filter so that it doesn't show up in status checks.
            RequestFilter::where('request_id', $id)->delete();
            $provider = Provider::find($UserRequest->provider_id);

            if ($provider->wallet_balance <= config('constants.minimum_negative_balance')) {
                ProviderService::where('provider_id', $provider->id)->update(['status' => 'balance']);
                Provider::where('id', $provider->id)->update(['status' => 'balance']);
            } else {
                ProviderService::where('provider_id', $provider->id)->update(['status' => 'active']);
            }

            (new SendPushNotification())->Rate($UserRequest);

            return response()->json(['message' => trans('api.ride.request_completed')]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.ride.request_not_completed')], 500);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function request_rides(Request $request)
    {
        $req      = $request->request_id;
        $provider = auth()->user()->id;

        try {
            if ($request->ajax()) {
                $query = UserRequest::query();
                $query->when(request('type') == 'past', function ($q) use ($req) {
                    $q->when(request('request_id') != null, function ($p) use ($req) {
                        $p->where('id', $req);
                    });
                    $q->where('status', 'COMPLETED');
                    $q->where('provider_id', auth()->user()->id);
                });
                $query->when(request('type') == 'upcoming', function ($q) use ($req) {
                    $q->when(request('request_id') != null, function ($p) use ($req) {
                        $p->where('id', $req);
                    });
                    $q->where('is_scheduled', 'YES');
                    $q->where('provider_id', auth()->user()->id);
                });
                $Jobs = $query->orderBy('created_at', 'desc')
                    ->with('payment', 'service_type', 'user', 'rating')
                    ->get();

                if (!empty($Jobs)) {
                    $map_icon_start = asset('asset/img/marker-car.png');
                    $map_icon_end   = asset('asset/img/map-marker-red.png');
                    foreach ($Jobs as $key => $value) {
                        $Jobs[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                            'autoscale=10' .
                            '&size=600x300' .
                            '&maptype=terrian' .
                            '&format=png' .
                            '&visual_refresh=true' .
                            '&markers=icon:' . $map_icon_start . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                            '&markers=icon:' . $map_icon_end . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                            '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                            '&key=' . config('constants.server_map_key');
                    }
                }
                return $Jobs;
            }
        } catch (Exception $e) {
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function scheduled(Request $request)
    {
        try {
            $Jobs = UserRequest::where('provider_id', auth()->user()->id)
                ->where('status', 'SCHEDULED')
                ->where('is_scheduled', 'YES')
                ->with('payment', 'service_type')
                ->get();

            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end   = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=600x300' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon_start . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon_end . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }

            return $Jobs;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        if ($request->ajax()) {
            $Jobs = UserRequest::where('provider_id', auth()->user()->id)
                ->where('status', 'COMPLETED')
                ->orderBy('created_at', 'desc')
                ->with('payment', 'service_type')
                ->get();

            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end   = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=600x300' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon_start . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon_end . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }
            return $Jobs;
        }
        $Jobs = UserRequest::where('provider_id', auth()->guard('provider')->user()->id)->with('user', 'service_type', 'payment', 'rating')->get();
        return view('provider.trip.index', compact('Jobs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        //try {
        $UserRequest = UserRequest::with('user')->findOrFail($id);

        //TODO ALLAN - Correção bug das viagens aceitas no aplicativo motorista
        if ($UserRequest->current_provider_id != auth()->user()->id && config('constants.broadcast_request', 0) == 0) {
            return response()->json(['error' => 'Tempo esgotado!']);
        }

        if ($UserRequest->status != 'SEARCHING') {
            return response()->json(['error' => trans('api.ride.request_inprogress')]);
        }

        $UserRequest->provider_id = auth()->user()->id;

        if (config('constants.broadcast_request', 0) == 1) {
            $UserRequest->current_provider_id = auth()->user()->id;
        }

        if ($UserRequest->schedule_at != '') {
            $beforeschedule_time = strtotime($UserRequest->schedule_at . '- 1 hour');
            $afterschedule_time  = strtotime($UserRequest->schedule_at . '+ 1 hour');

            $CheckScheduling = UserRequest::where('status', 'SCHEDULED')
                ->where('provider_id', auth()->user()->id)
                ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                ->count();

            if ($CheckScheduling > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_already_scheduled'));
                }
            }

            RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', auth()->user()->id)->update(['status' => 2]);

            $UserRequest->status  = 'SCHEDULED';
            $UserRequest->save();
        } else {
            //Processa pagemento de teste para verificar se o cartão tem saldo
            if ($UserRequest->payment_mode == 'CARD') {
                $User        = User::where('id', $UserRequest->user_id)->first();
                $amount      = (string) $UserRequest->estimated_fare;
                $amount      = str_replace('.', '', $amount);
                $amount      = (int) $amount;
                $totalAmount = $amount;
                $random      = config('constants.booking_prefix') . mt_rand(100000, 999999);

                $Card = Card::where('user_id', $UserRequest->user_id)->where('is_default', 1)->first();
                if ($Card == null) {
                    $Card = Card::where('user_id', $UserRequest->user_id)->first();
                }
                $gateway    = new PaymentGateway('stripe');
                $attributes = [
                    'order'         => $random,
                    'amount'        => $totalAmount,
                    'currency'      => config('constants.stripe_currency'),
                    'customer'      => $User->stripe_cust_id,
                    'card'          => $Card->card_id,
                    'description'   => 'Verificação de cartão ' . $User->email,
                    'receipt_email' => $User->email,
                ];

                try {
                    \Stripe\Stripe::setApiKey(config('constants.stripe_secret_key', ''));

                    if (!empty($attributes['type']) && $attributes['type'] == 'connected_account') {
                        $Charge = \Stripe\Charge::create([
                            'amount'   => $attributes['amount'],
                            'currency' => $attributes['currency'],
                            'source'   => $attributes['customer'],
                            'capture'  => false,
                        ]);
                    } else {
                        $Charge = \Stripe\Charge::create([
                            'amount'        => $attributes['amount'],
                            'currency'      => $attributes['currency'],
                            'customer'      => $attributes['customer'],
                            'card'          => $attributes['card'],
                            'description'   => $attributes['description'],
                            'receipt_email' => $attributes['receipt_email'],
                            'capture'       => false,
                        ]);
                    }

                    $paymentId = $Charge['id'];

                    //Se a transação for bem sucedida extorna o valor pago para o passageiro
                    $refund = \Stripe\Refund::create([
                        'charge' => $paymentId,
                    ]);
                } catch (\Stripe\Error\Card $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (\Stripe\Error\RateLimit $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (\Stripe\Error\InvalidRequest $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (\Stripe\Error\Authentication $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (\Stripe\Error\ApiConnection $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (\Stripe\Error\Base $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                } catch (Exception $e) {
                    // Send Push Notification to User
                    (new SendPushNotification())->sendPushToProvider(auth()->user()->id, 'User card failure! Trip canceled.');
                    (new SendPushNotification())->sendPushToUser($UserRequest->user_id, 'Payment processing failed! Please check your card.');
                    $this->cancel($request);
                    return response()->json(['error' => 'User card failure! Trip canceled.'], 422);
                }
            }

            $UserRequest->status  = 'STARTED';
            $UserRequest->save();

            ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'riding']);

            $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', auth()->user()->id)->get();
            // dd($Filters->toArray());
            foreach ($Filters as $Filter) {
                $Filter->delete();
            }
        }

        $UnwantedRequest = RequestFilter::where('request_id', '!=', $UserRequest->id)
            ->where('provider_id', auth()->user()->id)
            ->whereHas('request', function ($query) {
                $query->where('status', '<>', 'SCHEDULED');
            });

        if ($UnwantedRequest->count() > 0) {
            $UnwantedRequest->delete();
        }

        // Send Push Notification to User
        (new SendPushNotification())->RideAccepted($UserRequest);

        return $UserRequest;
        //} catch (ModelNotFoundException $e) {
        //    return response()->json(['error' => trans('api.unable_accept')]);
        //} catch (Exception $e) {
        //    return response()->json(['error' => trans('api.connection_err')]);
        //}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
        ]);

        try {
            $UserRequest = UserRequest::with('user')->findOrFail($id);

            if ($request->status == 'DROPPED' && $request->d_latitude != null && $request->d_longitude != null) {
                $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $UserRequest->s_latitude . ',' . $UserRequest->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.server_map_key');

                $json = curl($details);

                $details = json_decode($json, true);

                $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

                $UserRequest->route_key = $route_key;
            }

            if ($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
                $UserRequest->status = 'DROPPED';
                $UserRequest->paid   = 0;

                (new SendPushNotification())->Complete($UserRequest);
            } elseif ($request->status == 'COMPLETED') {
                //Processa pagamento com cartão de crédito
                if ($request->payment_mode == 'CARD') {
                    if ($UserRequest->status == 'COMPLETED') {
                        //for off cross clicking on change payment issue on mobile
                        return true;
                    }

                    $tip_amount     = 0;
                    $random         = config('constants.booking_prefix') . mt_rand(100000, 999999);
                    $RequestPayment = UserRequestPayment::where('request_id', $request->request_id)->first();
                    $User           = User::where('id', $UserRequest->user_id)->first();

                    if (isset($request->tips) && !empty($request->tips)) {
                        $tip_amount = round($request->tips, 2);
                    }

                    $totalAmount = $RequestPayment->payable + $tip_amount;

                    if ($totalAmount == 0) {
                        $UserRequest->payment_mode    = 'CARD';
                        $RequestPayment->card         = $RequestPayment->payable;
                        $RequestPayment->payable      = 0;
                        $RequestPayment->tips         = $tip_amount;
                        $RequestPayment->provider_pay = $RequestPayment->provider_pay + $tip_amount;
                        $RequestPayment->save();

                        $UserRequest->paid   = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();

                        //for create the transaction
                        Log::debug('transaction hit');
                        (new TransactionResource())->callTransaction($request->request_id);

                        if ($request->ajax()) {
                            return response()->json(['message' => trans('api.paid')]);
                        } else {
                            return redirect('dashboard')->with('flash_success', trans('api.paid'));
                        }
                    } else {
                        $log                   = new PaymentLog();
                        $log->user_type        = 'user';
                        $log->transaction_code = $random;
                        $log->amount           = $totalAmount;
                        $log->transaction_id   = $UserRequest->id;
                        $log->payment_mode     = $request->payment_mode;
                        $log->user_id          = $UserRequest->user_id;
                        $log->save();

                        $Card = Card::where('user_id', $UserRequest->user_id)->where('is_default', 1)->first();

                        if ($Card == null) {
                            $Card = Card::where('user_id', $UserRequest->user_id)->first();
                        }

                        $gateway = new PaymentGateway('stripe');
                        return $gateway->process([
                            'order'         => $random,
                            'amount'        => $totalAmount,
                            'currency'      => config('constants.stripe_currency'),
                            'customer'      => $User->stripe_cust_id,
                            'card'          => $Card->card_id,
                            'description'   => 'Ride Payment ' . $User->email,
                            'receipt_email' => $User->email,
                        ]);
                    }
                } else {
                    if ($UserRequest->status == 'COMPLETED') {
                        //for off cross clicking on change payment issue on mobile
                        return true;
                    }

                    $UserRequest->status       = $request->status;
                    $UserRequest->paid         = 1;
                    $UserRequest->payment_mode = ($request->payment_mode ? $request->payment_mode : $UserRequest->payment_mode);

                    (new SendPushNotification())->Complete($UserRequest);

                    //for completed payments
                    $RequestPayment               = UserRequestPayment::where('request_id', $id)->first();
                    $RequestPayment->payment_mode = ($request->payment_mode ? $request->payment_mode : $UserRequest->payment_mode);
                    $RequestPayment->cash         = $RequestPayment->payable;
                    $RequestPayment->payable      = 0;
                    $RequestPayment->save();
                }
            } else {
                $UserRequest->status = $request->status;

                if ($request->status == 'ARRIVED') {
                    (new SendPushNotification())->Arrived($UserRequest);
                }
            }

            if ($request->status == 'PICKEDUP') {
                if (isset($request->otp)) {
                    if ($request->otp == $UserRequest->otp) {
                        if ($UserRequest->is_track == 'YES') {
                            //$UserRequest->distance  = 0;
                        }
                        $UserRequest->started_at = Carbon::now();

                        (new SendPushNotification())->Pickedup($UserRequest);
                    } else {
                        return response()->json(['error' => trans('api.otp')]);
                    }
                } else {
                    if ($UserRequest->is_track == 'YES') {
                        //$UserRequest->distance  = 0;
                    }
                    $UserRequest->started_at = Carbon::now();
                    (new SendPushNotification())->Pickedup($UserRequest);
                }
            }


            \DB::transaction(function () use ($UserRequest, $request, $id) { // Start the transaction

                $UserRequest->save();


                if ($request->status == 'DROPPED') {
                    if ($UserRequest->is_track == 'YES') {
                        $UserRequest->track_distance  = 1000;
                        $UserRequest->track_latitude  = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->track_longitude = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->d_latitude      = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->d_longitude     = $request->longitude ?: $UserRequest->d_longitude;
                        $UserRequest->d_address       = getAddress($request->latitude, $request->longitude);
                        $details                      = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $request->s_latitude . ',' . $request->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.server_map_key');
                        $json                         = curl($details);
                        $details                      = json_decode($json, true);
                        $route_key                    = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';
                        $UserRequest->route_key       = $route_key;
                    }

                    //Calcula distância final com localização do motorista
                    if ($request->ajax()) {
                        $Provider = auth()->user();
                    } else {
                        $Provider = auth()->guard('provider')->user();
                    }
                    $locationarr            = ['s_latitude' => $UserRequest->s_latitude, 's_longitude' => $UserRequest->s_longitude, 'd_latitude' => $Provider->latitude, 'd_longitude' => $Provider->longitude];
                    $UserRequest->distance  = $this->getLocationDistance($locationarr);
                    $UserRequest->d_address = getAddress($Provider->latitude, $Provider->longitude);

                    $UserRequest->finished_at = Carbon::now();
                    $StartedDate              = date_create($UserRequest->started_at);
                    $FinisedDate              = Carbon::now();
                    $TimeInterval             = date_diff($StartedDate, $FinisedDate);
                    $MintuesTime              = $TimeInterval->i;
                    $UserRequest->travel_time = $MintuesTime;
                    $UserRequest->d_latitude  = $Provider->latitude;
                    $UserRequest->d_longitude = $Provider->longitude;
                    $UserRequest->save();
                    $UserRequest->with('user')->findOrFail($id);
                    $UserRequest->invoice = $this->invoice($id, ($request->toll_price != null) ? $request->toll_price : 0);

                    (new SendPushNotification())->Dropped($UserRequest);
                }
            }); // End transaction
            //for completed payments
            (new TransactionResource())->callTransaction($id);

            // Send Push Notification to User

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err').$e]);
        }
    }

    /**
     * Verifica distância entre 2 pontos
     *
     * @return \Illuminate\Http\Response
     */
    public function getLocationDistance($locationarr)
    {
        $fn_response = ['data' => null, 'errors' => null];

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
            $fn_response['meter']   = $location['rows'][0]['elements'][0]['distance']['value'] ?? 0;
            $fn_response['time']    = $location['rows'][0]['elements'][0]['duration']['text'];
            $fn_response['seconds'] = $location['rows'][0]['elements'][0]['duration']['value'];
        } catch (Exception $e) {
            $fn_response['errors'] = trans('user.maperror');
        }
        return round($fn_response['meter'] / 1000, 1); //RETORNA QUILÔMETROS
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $UserRequest = UserRequest::find($id);

        $requestdelete = RequestFilter::where('request_id', $id)
            ->where('provider_id', auth()->user()->id)
            ->delete();

        try {
            if (config('constants.broadcast_request') == 1) {
                return response()->json(['message' => trans('api.ride.request_rejected')]);
            } else {
                $this->assign_next_provider($UserRequest->id);
                return $UserRequest->with('user')->get();
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    public function test(Request $request)
    {
        //$push =  (new SendPushNotification)->IncomingRequest($request->id);
        $push = (new SendPushNotification())->Arrived($request->user_id);

        dd($push);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function assign_destroy($id)
    {
        $UserRequest = UserRequest::find($id);
        try {
            UserRequest::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification())->ProviderNotAvailable($UserRequest->user_id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function assign_next_provider($request_id)
    {
        try {
            $UserRequest = UserRequest::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        $RequestFilter = RequestFilter::where('provider_id', $UserRequest->current_provider_id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        try {
            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                ->orderBy('id')
                ->firstOrFail();

            $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at         = Carbon::now();
            $UserRequest->save();

            // incoming request push to provider
            (new SendPushNotification())->IncomingRequest($next_provider->provider_id);
        } catch (ModelNotFoundException $e) {
            UserRequest::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();

            //  request push to user provider not available
            (new SendPushNotification())->ProviderNotAvailable($UserRequest->user_id);
        }
    }

    public function invoice($request_id, $toll_price = 0)
    {
        try {
            $UserRequest                    = UserRequest::with('provider')->with('service_type')->findOrFail($request_id);
            $tax_percentage                 = config('constants.tax_percentage', 0);
            $commission_percentage          = config('constants.commission_percentage', 0);
            $provider_commission_percentage = config('constants.provider_commission_percentage');

            $Fixed              = 0;
            $Distance           = 0;
            $Discount           = 0; // Promo Code discounts should be added here.
            $Wallet             = 0;
            $Surge              = 0;
            $item               = $UserRequest->price;
            $ProviderCommission = 0;
            $ProviderPay        = 0;
            $Distance_fare      = 0;
            $Minute_fare        = 0;
            $calculator         = 'DISTANCE';
            $discount_per       = 0;

            //added the common function for calculate the price
            $requestarr['kilometer']    = $UserRequest->distance;
            $requestarr['time']         = 0;
            $requestarr['seconds']      = 0;
            $requestarr['minutes']      = $UserRequest->travel_time;
            $requestarr['service_type'] = $UserRequest->service_type_id;

            $requestarr['s_latitude'] = $UserRequest->s_latitude;
            $requestarr['s_longitude'] = $UserRequest->s_longitude;
            $requestarr['d_latitude'] = $UserRequest->d_latitude;
            $requestarr['d_longitude'] = $UserRequest->d_longitude;
            $requestarr['service_required'] = $UserRequest->service_required;
            $requestarr['rental_hours'] = $UserRequest->rental_hours;

            $requestarr['leave'] = $UserRequest->out_leave ?? 0;
            $requestarr['return'] = $UserRequest->out_return ?? 0;
            $requestarr['day'] = $UserRequest->day ?? 0;
            $response  = new ServiceTypes();
            $pricedata = $response->applyPriceLogic($requestarr, 1);
            \Log::alert($pricedata);
            if (!empty($pricedata)) {
                $Distance      = $pricedata['price'] + $item;
                $Fixed         = ($pricedata['base_price'] < $pricedata['price'] ? $pricedata['price'] : $pricedata['base_price']);
                $Distance_fare = $pricedata['distance_fare'];
                $Minute_fare   = $pricedata['minute_fare'];
                $Hour_fare     = $pricedata['hour_fare'];
                $calculator    = $pricedata['calculator'];
            }

            $Distance = $Distance;
            $Tax      = ($Distance) * ($tax_percentage / 100);

            if ($UserRequest->promocode_id > 0) {
                if ($Promocode = Promocode::find($UserRequest->promocode_id)) {
                    $max_amount   = $Promocode->max_amount;
                    $discount_per = $Promocode->percentage;

                    $discount_amount = (($Distance + $Tax) * ($discount_per / 100));

                    if ($discount_amount > $Promocode->max_amount) {
                        $Discount = $Promocode->max_amount;
                    } else {
                        $Discount = $discount_amount;
                    }

                    $PromocodeUsage               = new PromocodeUsage();
                    $PromocodeUsage->user_id      = $UserRequest->user_id;
                    $PromocodeUsage->promocode_id = $UserRequest->promocode_id;
                    $PromocodeUsage->status       = 'USED';
                    $PromocodeUsage->save();

                    $Total          = $Distance + $Tax;
                    $payable_amount = $Distance + $Tax - $Discount;
                }
            }

            $Total          = $Distance + $Tax;
            $payable_amount = $Distance + $Tax - $Discount;

            if ($UserRequest->surge) {
                $Surge = (config('constants.surge_percentage') / 100) * $payable_amount;
                $Total += $Surge;
                $payable_amount += $Surge;
            }

            if ($Total < 0) {
                $Total          = 0.00; // prevent from negative value
                $payable_amount = 0.00;
            }

            //changed by tamil
            $Commision = ($Total) * ($commission_percentage / 100);

            $ProviderCommission = 0;
            $ProviderPay        = (($Total + $Discount) - $Commision) - $Tax;

            $Payment             = new UserRequestPayment();
            $Payment->request_id = $UserRequest->id;

            $Payment->user_id     = $UserRequest->user_id;
            $Payment->provider_id = $UserRequest->provider_id;

            //check peakhours and waiting charges
            $total_waiting_time = $total_waiting_amount = $peakamount = $peak_comm_amount = $waiting_comm_amount = 0;

            if ($UserRequest->service_type->waiting_min_charge > 0) {
                $total_waiting = round($this->total_waiting($UserRequest->id) / 60);
                if ($total_waiting > 0) {
                    if ($total_waiting > $UserRequest->service_type->waiting_free_mins) {
                        $total_waiting_time   = $total_waiting - $UserRequest->service_type->waiting_free_mins;
                        $total_waiting_amount = $total_waiting_time * $UserRequest->service_type->waiting_min_charge;
                        $waiting_comm_amount  = (config('constants.waiting_percentage') / 100) * $total_waiting_amount;
                    }
                }
            }

            $start_time = $UserRequest->started_at;
            $end_time   = $UserRequest->finished_at;

            $start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();
            $end_time_check   = PeakHour::where('start_time', '<=', $end_time)->where('end_time', '>=', $end_time)->first();

            if ($start_time_check) {
                $Peakcharges = ServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->first();

                if ($Peakcharges) {
                    $peakamount       = ($Peakcharges->min_price / 100) * $Fixed;
                    $peak_comm_amount = (config('constants.peak_percentage') / 100) * $peakamount;
                }
            } else {
                if ($end_time_check) {
                    $Peakcharges = ServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->first();

                    if ($Peakcharges) {
                        $peakamount       = ($Peakcharges->min_price / 100) * $Fixed;
                        $peak_comm_amount = (config('constants.peak_percentage') / 100) * $peakamount;
                    }
                }
            }

            if ($start_time_check || $end_time_check) {
                $Peakcharges = ServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->first();
                if ($Peakcharges) {
                    $Total += $peakamount + $total_waiting_amount + $toll_price;
                    $payable_amount += $peakamount + $total_waiting_amount + $toll_price;

                    $ProviderPay = $ProviderPay + ($peakamount + $total_waiting_amount) + $toll_price;
                } else {
                    $Total += $peakamount + $total_waiting_amount + $toll_price;
                    $payable_amount += $peakamount + $total_waiting_amount + $toll_price;

                    $ProviderPay = $ProviderPay + ($peakamount + $total_waiting_amount) + $toll_price;
                }
            } else {
                $Total += $peakamount + $total_waiting_amount + $toll_price;
                $payable_amount += $peakamount + $total_waiting_amount + $toll_price;

                $ProviderPay = $ProviderPay + ($peakamount + $total_waiting_amount) + $toll_price;
            }

            //Verifica se existe horário de pico

            /*
                 * Reported by Jeya, We are adding the surge price with Base price of Service Type.
                 */
            $Payment->fixed               = $Fixed + $Surge;
            $Payment->distance            = $Distance_fare;
            $Payment->minute              = $Minute_fare;
            $Payment->hour                = $Hour_fare;
            $Payment->commision           = $Commision;
            $Payment->commision_per       = $commission_percentage;
            $Payment->surge               = $Surge;
            $Payment->toll_charge         = $toll_price;
            $Payment->total               = $Total;
            $Payment->provider_commission = $ProviderCommission;
            $Payment->provider_pay        = $ProviderPay;
            $Payment->peak_amount         = $peakamount;
            $Payment->peak_comm_amount    = $peak_comm_amount;
            $Payment->total_waiting_time  = $total_waiting_time;
            $Payment->waiting_amount      = $total_waiting_amount;
            $Payment->waiting_comm_amount = $waiting_comm_amount;
            if ($UserRequest->promocode_id > 0) {
                $Payment->promocode_id = $UserRequest->promocode_id;
            }
            $Payment->discount     = $Discount;
            $Payment->discount_per = $discount_per;

            if ($Discount == ($Distance + $Tax)) {
                $UserRequest->paid = 1;
            }

            if ($UserRequest->use_wallet == 1 && $payable_amount > 0) {
                $User = User::find($UserRequest->user_id);

                $Wallet = $User->wallet_balance;

                if ($Wallet != 0) {
                    if ($payable_amount > $Wallet) {
                        $Payment->wallet     = $Wallet;
                        $Payment->is_partial = 1;
                        $Payable             = $payable_amount - $Wallet;

                        $Payment->payable = abs($Payable);

                        $wallet_det = $Wallet;
                    } else {
                        $Payment->payable = 0;
                        $WalletBalance    = $Wallet - $payable_amount;

                        $Payment->wallet = $payable_amount;

                        $Payment->payment_id   = 'WALLET';
                        $Payment->payment_mode = $UserRequest->payment_mode;

                        $UserRequest->paid   = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();

                        $wallet_det = $payable_amount;
                    }

                    // charged wallet money push
                    //(new SendPushNotification())->ChargedWalletMoney($UserRequest->user_id, currency($wallet_det));

                    //for create the user wallet transaction
                    //$response  = new TransactionResource();
                    (new TransactionResource())->userCreditDebit($wallet_det, $UserRequest, 0);
                }
            } else {
                //                if ($UserRequest->payment_mode == 'CASH') {
                //                    $Payment->round_of = round($payable_amount) - abs($payable_amount);
                //                    $Payment->total = $Total;
                //                    $Payment->payable = round($payable_amount);
                //                } else {
                //                    $Payment->total = abs($Total);
                //                    $Payment->payable = abs($payable_amount);
                //                }
                $Payment->total   = abs($Total);
                $Payment->payable = abs($payable_amount);
            }

            $Payment->tax     = $Tax;
            $Payment->tax_per = $tax_percentage;
            $Payment->save();

            return $Payment;
        } catch (ModelNotFoundException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_trips()
    {
        try {
            $UserRequests = UserRequest::ProviderUpcomingRequest(auth()->user()->id)->get();
            if (!empty($UserRequests)) {
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {
            $Jobs = UserRequest::where('id', $request->request_id)
                ->where('provider_id', auth()->user()->id)
                ->with('service_type', 'user', 'payment')
                ->get();
            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end   = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=600x300' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon_start . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon_end . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }

            return $Jobs[0];
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        try {
            if ($request->ajax()) {
                $rides = UserRequest::where('provider_id', auth()->user()->id)->count();

                $revenue_total = UserRequestPayment::whereHas('request', function ($query) use ($request) {
                    $query->where('provider_id', auth()->user()->id);
                })
                    ->sum('total');
                $revenue_commission = UserRequestPayment::whereHas('request', function ($query) use ($request) {
                    $query->where('provider_id', auth()->user()->id);
                })
                    ->sum('provider_commission');

                $revenue =  $revenue_total - $revenue_commission;

                $revenue         = UserRequestPayment::where('provider_id', auth()->user()->id)->sum('provider_pay');
                $cancel_rides    = UserRequest::where('status', 'CANCELLED')->where('provider_id', auth()->user()->id)->count();
                $completed_rides = UserRequest::where('status', 'COMPLETED')->where('provider_id', auth()->user()->id)->count();

                if ($request->has('data')) {
                    $montly_gains = UserRequestPayment::whereHas('request', function ($query) use ($request) {
                        $query->whereRaw('MONTH(created_at) = ' . str_replace('0', '', $request->get('data')));
                    })->where('provider_id', auth()->user()->id)->sum('provider_pay');

                    $montly_pass = UserRequestPayment::whereHas('request', function ($query) use ($request) {
                        $query->whereRaw('MONTH(created_at) = ' . str_replace('0', '', $request->get('data')));
                    })
                        ->where('provider_id', auth()->user()->id)
                        ->select(DB::raw('SUM((total - provider_pay)) as total_pass'))
                        ->get()->first();
                } else {
                    $montly_gains = 0.0;
                    $montly_pass  = 0.0;
                }

                return response()->json([
                    'rides'           => $rides,
                    'revenue'         => $revenue,
                    'montly_pass'     => $montly_pass->total_pass ?? 0,
                    'montly_gains'    => $montly_gains,
                    'cancel_rides'    => $cancel_rides,
                    'completed_rides' => $completed_rides,
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * help Details.
     *
     * @return \Illuminate\Http\Response
     */
    public function help_details(Request $request)
    {
        try {
            if ($request->ajax()) {
                return response()->json([
                    'contact_number' => config('constants.contact_number', ''),
                    'contact_email'  => config('constants.contact_email', ''),
                ]);
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    public function transferlist(Request $request)
    {
        $start_node = $request->start_node;
        $limit      = $request->limit;

        $pendinglist = WalletRequest::where('from_id', auth()->user()->id)->where('request_from', 'provider')->where('status', 0);
        if (!empty($limit)) {
            $pendinglist = $pendinglist->offset($start_node);
            $pendinglist = $pendinglist->limit($limit);
        }
        $pendinglist = $pendinglist->orderBy('id', 'desc')->get();

        return response()->json(['pendinglist' => $pendinglist, 'wallet_balance' => auth()->user()->wallet_balance]);
    }

    public function waiting(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        $user_id = UserRequest::find($request->id)->user_id;

        if ($request->has('status')) {
            $waiting = RequestWaitingTime::where('request_id', $request->id)->whereNull('ended_at')->first();

            if ($waiting != null) {
                $waiting->ended_at     = Carbon::now();
                $waiting->waiting_mins = Carbon::parse($waiting->started_at)->diffInSeconds(Carbon::now());
                $waiting->save();
            } else {
                $waiting             = new RequestWaitingTime();
                $waiting->request_id = $request->id;
                $waiting->started_at = Carbon::now();
                $waiting->save();
            }

            (new SendPushNotification())->ProviderWaiting($user_id, $request->status);
        }

        return response()->json(['waitingTime' => (int)$this->total_waiting($request->id), 'waitingStatus' => (int)$this->waiting_status($request->id)]);
    }

    public function total_waiting($id)
    {
        $waiting = RequestWaitingTime::where('request_id', $id)->whereNotNull('ended_at')->sum('waiting_mins');

        $uncounted_waiting = RequestWaitingTime::where('request_id', $id)->whereNull('ended_at')->first();

        if ($uncounted_waiting != null) {
            $waiting += Carbon::parse($uncounted_waiting->started_at)->diffInSeconds(Carbon::now());
        }

        return $waiting;
    }

    public function waiting_status($id)
    {
        $waiting = RequestWaitingTime::where('request_id', $id)->whereNull('ended_at')->first();

        return ($waiting != null) ? 1 : 0;
    }

    public function settlements($id)
    {
        $response  = new TransactionResource();

        $request_data = WalletRequest::where('id', $id)->first();

        if ($request_data->type == 'D') {
            $settle_amt  = -1 * $request_data->amount;
            $admin_amt   = -1 * abs($request_data->amount);
            $settle_msg  = 'Settlement debit';
            $ad_msg      = 'Settlement debit';
            $settle_type = $request_data->type;
            $ad_type     = $request_data->type;
        } else {
            $settle_amt  = $request_data->amount;
            $admin_amt   = $request_data->amount;
            $settle_msg  = 'Settlement credit';
            $ad_msg      = 'Settlement credit';
            $settle_type = $request_data->type;
            $ad_type     = $request_data->type;
        }

        if ($request_data->request_from == 'provider') {
            $ipdata                      = [];
            $ipdata['transaction_id']    = $request_data->id;
            $ipdata['transaction_alias'] = $request_data->alias_id;
            $ipdata['transaction_desc']  = $settle_msg;
            $ipdata['id']                = $request_data->from_id;
            $ipdata['type']              = $settle_type;
            $ipdata['amount']            = $settle_amt;
            $response->createProviderWallet($ipdata);
            $transaction_type = 5;
        } else {
            $ipdata                      = [];
            $ipdata['transaction_id']    = $request_data->id;
            $ipdata['transaction_alias'] = $request_data->alias_id;
            $ipdata['transaction_desc']  = $settle_msg;
            $ipdata['id']                = $request_data->from_id;
            $ipdata['type']              = $settle_type;
            $ipdata['amount']            = $settle_amt;
            $response->createAgentWallet($ipdata);
            $transaction_type = 8;
        }

        $ipdata                      = [];
        $ipdata['transaction_id']    = $request_data->id;
        $ipdata['transaction_alias'] = $request_data->alias_id;
        $ipdata['transaction_desc']  = $ad_msg;
        $ipdata['transaction_type']  = $transaction_type;
        $ipdata['type']              = $ad_type;
        $ipdata['amount']            = $admin_amt;
        $response->createAdminWallet($ipdata);

        $request_data->status = 1;
        $request_data->save();

        return true;
    }

    public function track_location(Request $request)
    {
        //$UserRequest = DB::table('location_points')->first();
        //->first();
        $path = $request->all();
        if ($request->status == 'multiple') {
            //$all_path = json_decode($UserRequest->provider_path);
            $curr_path = $request->all();
            unset($curr_path['status']);
            //$all_path = [];
            if (count($curr_path) > 0) {
                foreach ($curr_path as $key => $item) {
                    //$item['mobtime'] = $item['time'];
                    $item['servertime'] = Carbon::now();
                    $path               = $item;
                    //unset($path['time']);
                    DB::table('location_points')->insert([$path]);
                }
                /* $json_path = json_encode($all_path);
                  $UserRequest->provider_path = $json_path;
                  $UserRequest->save(); */
            }
            //return response()->json(['order_path'=>$all_path]);
        } else {
            $path['servertime'] = Carbon::now();
            //$path['mobtime'] = $path['time'];
            //unset($path['time']);
            unset($path['status']);
            DB::table('location_points')->insert([$path]);
            /* if($UserRequest->provider_path){
              $all_path = json_decode($UserRequest->provider_path);
              }else{
              $all_path = [];
              }
              $all_path [] =$path ;
              $json_path = json_encode($all_path);
              $UserRequest->provider_path = $json_path;
              $UserRequest->save(); */
        }
        $UserRequest = DB::table('location_points')->get();
        return [];
        //return response()->json(['order_path'=>$UserRequest]);
    }

    public function track_location_remove(Request $request)
    {
        DB::table('location_points')->truncate();
        return [];
    }

    public function track_location_get(Request $request)
    {
        if ($request->notes != '') {
            $location_points = DB::table('location_points')->where('notes', 'LIKE', "%{$request->notes}%")->get();
        } else {
            $location_points = DB::table('location_points')->get();
        }

        return response()->json(['location_points' => $location_points]);
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {
            $Jobs = UserRequest::where('id', $request->request_id)
                ->where('provider_id', auth()->user()->id)
                ->with('payment', 'service_type', 'user', 'rating')
                ->get();
            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end   = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=600x300' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon_start . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon_end . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }

                $Jobs[0]->dispute = UserRequestDispute::
                //where('dispute_type', 'provider')->
                where('request_id', $request->request_id)->where('user_id', auth()->user()->id)->first();

                $Jobs[0]->contact_number = config('constants.contact_number', '');
                $Jobs[0]->contact_email  = config('constants.contact_email', '');
            }

            return $Jobs[0];
        }
    }

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
        $location = directionGoogle($s_latitude, $s_longitude, $d_latitude, $d_longitude);
        return $location;
    }

    public function wallet_transation(Request $request)
    {
        try {
            $start_node = $request->start_node;
            $limit      = $request->limit;

            //$wallet_transation = ProviderWallet::where('provider_id',auth()->user()->id);

            $wallet_transation = ProviderWallet::with('transactions')->orderBy('id', 'desc')->select('transaction_alias', \DB::raw('SUM(amount) as amount'))->where('provider_id', auth()->user()->id)->groupBy('transaction_alias');

            if (!empty($limit)) {
                $wallet_transation = $wallet_transation->offset($start_node);
                $wallet_transation = $wallet_transation->limit($limit);
            }

            $wallet_transation = $wallet_transation->get();

            foreach ($wallet_transation as $key => $svalue) {
                $wallet_transation[$key]->created_at = $svalue->transactions[0]->created_at;
            }

            return response()->json(['wallet_transation' => $wallet_transation, 'wallet_balance' => auth()->user()->wallet_balance]);
        } catch (Exception $e) {
            dump($e);
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function wallet_details(Request $request)
    {
        try {
            $wallet_details = ProviderWallet::where('transaction_alias', 'LIKE', $request->alias_id)->where('provider_id', auth()->user()->id)->get();

            return response()->json(['wallet_details' => $wallet_details]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }
}
