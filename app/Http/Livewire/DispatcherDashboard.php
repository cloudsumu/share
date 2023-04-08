<?php

namespace App\Http\Livewire;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\RequestFilter;
use App\Models\ProviderService;
use App\Helpers\ControllerHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\Admin\DispatcherController;
use App\Http\Controllers\Api\User\RequestApiController;
use Illuminate\Support\Facades\DB;
use stdClass;

class DispatcherDashboard extends Component
{
    // use WithPagination;

    protected $listeners = ['update_s_coordinates', 'update_d_coordinates', 'update_distance'];

    protected $queryString = ['active'];

    public $active;
    public $requests;
    public $services;
    
    // Creating Request Properties
    public $user_id;
    public $provider_id;
    public $service_type_id;
    public $d_longitude;
    public $d_latitude;
    public $s_longitude;
    public $s_latitude;
    public $distance;
    public $status;
    public $s_address;
    public $d_address;
    public $email;
    public $user_request_id;
    public $scheduled_at;
    public $booking_id;
    protected $available_providers;
    public $estimated_fare; 

    /**
     * Contains a collection of all the available users with the given identifier.
     * @var Collection
     */
    public $suggestions = [];

    public function update_d_coordinates($lat, $lng, $address)
    {
        $this->d_latitude = $lat;
        $this->d_longitude = $lng;
        $this->d_address = $address;
    }
    
    public function update_s_coordinates($lat, $lng, $address)
    {
        $this->s_latitude = $lat;
        $this->s_longitude = $lng;
        $this->s_address = $address;
    }

    public function update_distance($distance)
    {
        $this->distance = $distance;
    }

    public function createCredentialsForRequest(Request $request)
    {
        $this->booking_id = $credentials['booking_id'] = $this->booking_id ? $this->booking_id : generate_booking_id();
        $credentials['user_id'] = $this->user_id;
        $credentials['provider_id'] = $this->provider_id;
        $credentials['service_type_id'] = $this->service_type_id;

        $credentials['d_longitude'] = $this->d_longitude;
        $credentials['d_latitude'] = $this->d_latitude;
        $credentials['s_longitude'] = $this->s_longitude;
        $credentials['s_latitude'] = $this->s_latitude;

        $credentials['distance'] = $this->distance;
        $credentials['s_address'] = $this->s_address;
        $credentials['d_address'] = $this->d_address;

        // Fields Not Included
        $credentials['provider_id'] = 0;
        $credentials['status'] = 'SEARCHING';
        $credentials['cancelled_by'] = 'NONE';
        $credentials['payment_mode'] = 'CASH';
        $credentials['route_key'] = null;
        $credentials['current_provider_id'] = 0;
        
        $request['service_type'] = $this->service_type_id;
        $request['d_longitude'] = $this->d_longitude;
        $request['d_latitude'] = $this->d_latitude;
        $request['s_longitude'] = $this->s_longitude;
        $request['s_latitude'] = $this->s_latitude;
        $request['service_required'] = 'none';
        $request['rental_hours'] = 0;
        $request['validate'] = 0;
        $request['schedule_time'] = $this->scheduled_at;
        $request['otp'] = mt_rand(1000, 9999);
        $handleReq = new RequestApiController;
        $credentials['geo_fencing_id'] = $handleReq->poly_check_new($this->s_latitude, $this->s_longitude);
        $response = json_decode($handleReq->estimated_fare($request)->content());
        $this->estimated_fare = $credentials['estimated_fare'] = $response->estimated_fare;

        if(!empty($response->error)) { // If response had errors.
            return $response;
        }
        else {
            $request['use_wallet'] = 0;
            $request['distance'] = $this->distance;
            $request['payment_mode'] = 'CASH';

            $response = $this->store($request);
            return $response;
        }
    }

    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->validate($request, [
        //     's_latitude'   => 'required|numeric',
        //     's_longitude'  => 'required|numeric',
        //     'd_latitude'   => 'required|numeric',
        //     'd_longitude'  => 'required|numeric',
        //     'service_type' => 'required|numeric|exists:service_types,id',
        //     'distance'     => 'required|numeric',
        // ]);

        try {
            $User = User::where('mobile', $request->mobile)->firstOrFail();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $this->email)->firstOrFail();
            } catch (Exception $e) {
                // try {
                //     $User = User::create([
                //         'first_name'   => $request->first_name,
                //         'last_name'    => $request->last_name,
                //         'email'        => $request->email,
                //         'mobile'       => $request->mobile,
                //         'password'     => bcrypt($request->mobile),
                //         'payment_mode' => 'CASH',
                //     ]);
                // } catch(Exception $e) {
                //     Log::error($e->getMessage());
                // }

                $response = new stdClass;
                $response->error = 'User Does Not Exit. Create User First.';
                // throw new Exception('User Does Not Exit. Create User First.');

            }
        }

        if ($request->has('schedule_time')) {
            try {
                $CheckScheduling = UserRequest::where('status', 'SCHEDULED')
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
            //TODO ALLAN - Alterações Debit na máquina e voucher
            //Verifica se os tipos de pagamentos estão disponíveis
            if (config('constants.voucher', 1) == 0 && $request->payment_mode == 'VOUCHER') {
                return response()->json(['message' => 'Voucher payment disabled! Go to the payment setting to activate.']);
            } elseif (config('constants.cash', 1) == 0 && $request->payment_mode == 'CASH') {
                return response()->json(['message' => 'Cash payment disabled! Go to the payment setting to activate.']);
            } elseif (config('constants.debit_machine', 1) == 0 && $request->payment_mode == 'DEBIT_MACHINE') {
                return response()->json(['message' => 'Debit payment on the machine disabled! Go to the payment setting to activate.']);
            }

            $ActiveProviders = ProviderService::AvailableServiceDispatcherProvider($request->service_type)
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
                //->orderBy('distance', 'asc')
                ->get();

            // List Providers who are currently busy and add them to the filter list.

            if (count($Providers) == 0) {
                if ($request->ajax()) {
                    // Push Notification to User
                    return response()->json(['message' => trans('api.ride.no_providers_found')]);
                } else {
                    // throw new Exception('No Providers Available Currently.');
                    // return response()->json(['error' => 'No Providers Available Currently.']);
                    $response = new stdClass();
                    $response->error = 'No Providers Available Currently.';
                    return $response;
                }
            }

            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $request->s_latitude . ',' . $request->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.map_key');

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest                      = new UserRequest();
            $this->booking_id = $UserRequest->booking_id = $this->booking_id ? $this->booking_id : generate_booking_id();
            $UserRequest->user_id             = $User->id;
            $UserRequest->current_provider_id = 0;
            $UserRequest->service_type_id     = $request->service_type;
            $UserRequest->payment_mode        = 'CASH'; //$request->payment_mode;
            $UserRequest->promocode_id        = 0;
            $UserRequest->is_scheduled        = 'YES';
            $UserRequest->status              = 'SEARCHING';

            //TODO ALLAN - Alterações Debit na máquina e voucher

            //$UserRequest->comments = ( $request->comments ? $request->comments : null );

            //Calcula valor estimado da tarifa
            // try {
            //     $response = new ServiceTypes();

            //     $responsedata = $response->calculateFare($request->all(), 1);

            //     $UserRequest->estimated_fare = 0; //$responsedata['data']['estimated_fare'];
            // } catch (Exception $e) {
            //     return response()->json(['error' => $e->getMessage()], 500);
            // }

            $UserRequest->s_address   = $this->s_address ?: '';
            $UserRequest->s_latitude  = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_address   = $this->d_address ?: '';
            $UserRequest->d_latitude  = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->route_key   = $route_key;

            $UserRequest->distance = round($request->distance);

            // if ($request->has('provider_auto_assign')) {
            if ($request->status == 0) {
                $UserRequest->assigned_at = Carbon::now();
            }

            $UserRequest->use_wallet = 0;
            $UserRequest->surge      = 0;        // Surge is not necessary while adding a manual dispatch

            if ($request->has('schedule_time')) {
                $UserRequest->schedule_at = Carbon::parse($request->schedule_time);
            }

            try {
                $UserRequest->estimated_fare = $this->estimated_fare;
                $UserRequest->save();
            } catch(Exception $e) {
                Log::error($e->getMessage());
            }

            // if ($request->has('provider_auto_assign')) {
            //if($this->status == 0) {
                // $Providers[0]->service()->update(['status' => 'riding']);
                $UserRequest->current_provider_id = 0;
                // if ((config('constants.broadcast_request', 0) == 0)) {
                //     $UserRequest->current_provider_id = $Providers[0]->id;
                // } else {
                //     $UserRequest->current_provider_id = 0;
                // }

                $UserRequest->save();

                Log::info('New Dispatch : ' . $UserRequest->id);
                Log::info('Assigned Provider : ' . $UserRequest->current_provider_id);

                foreach ($Providers as $key => $Provider) {
                    if (config('constants.broadcast_request', 0) == 1) {
                        (new SendPushNotification())->IncomingRequest($Provider->id);
                    }

                    $Filter = new RequestFilter();
                    // Send push notifications to the first provider
                    // incoming request push to provider

                    $Filter->request_id  = $UserRequest->id;
                    $Filter->provider_id = $Provider->id;
                    $Filter->save();
                }
            //}

            // if ($request->ajax()) {
                return $UserRequest;
            // } else {
            //     return redirect('dashboard');
            // }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    public function emptyRequestCredentials()
    {
        $this->booking_id = null;
        $this->user_id = null;
        $this->provider_id = null;
        $this->service_type_id = null;
        $this->d_longitude = null;
        $this->d_latitude = null;
        $this->s_longitude = null;
        $this->s_latitude = null;
        $this->distance = null;
        $this->s_address = null;
        $this->d_address = null;

        // Fields Not Included
        $this->provider_id = null;
        $this->status = null;
        $this->cancelled_by = null;
        $this->payment_mode = null;
        $this->route_key = null;
        $this->email = null;
    }

    public function changeContent($value)
    {
        $tabs = ['searching', 'cancelled', 'scheduled', 'add', 'ongoing'];
        if(in_array($value, $tabs)) {
            $this->active = $value;
        }
    }

    public function selectUser($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user) {
            $this->user_id = $user_id;
            $this->email = $user->email;
        }
    }
 
    public function createRequest(Request $request)
    {
        // Include a proper validation instead of these if condition for all the entire UserRequest credentials.
        if(!empty($this->email)) {

            $user = User::where('email', $this->email)->first();

            if($user) {
                try {
                    $request->id = $user->id;
                    $response = $this->createCredentialsForRequest($request);
                    if($response instanceof UserRequest) {
                        $this->user_request_id = $response->id;
                        $this->emit('livewire_success', 'User Request Added Successfully');
                    }
                    
                    if(!empty($response->error)) {
                        throw new Exception($response->error);
                    }

                    // $credentials = $response;
                    // $user_request = UserRequest::create($credentials);
                    // $this->user_request_id = $user_request->id;
                    // $user_request = (new RequestApiController)->send_request($credentials);

                    if($this->status) {
                        // We have to assign Provider.
                        $request['latitude'] = $this->s_latitude;
                        $request['longitude'] = $this->s_longitude;
                        $request['service_type'] = $this->service_type_id;
                        $this->active = 'assign_provider';
                        // dd($findProvider->providers($request));
                        $this->available_providers = (new DispatcherController)->providers($request);
                    }
                    else {
                        $this->emit('clearMap');
                        $this->emptyRequestCredentials();
                    }
                } catch(Exception $e) {
                    $this->emit('livewire_error', $e->getMessage());
                }
            }
            else {
                $this->emit('livewire_error', 'Invalid Email Address');
                return true;
            }
        }
        else {
            $this->emit('livewire_error', 'Invalid Email Address');
            return true;
        }
    }

    public function appointProvider(UserRequest $user_request, Provider $provider)
    {
        $credentials['provider_id'] = $provider->id;
        $user_request->update($credentials);
        $this->user_request_id = null;
        $this->active = 'add';
        $this->emptyRequestCredentials();
        $this->emit('clearMap');
        $this->emit('livewire_success', 'Provider Appointed To Request Successfully');
    }

    public function getRequests()
    {
        if($this->active == 'ongoing') {
            $this->requests = UserRequest::whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'SCHEDULED'])->get();
        }
        elseif($this->active == 'scheduled') {
            $this->requests = UserRequest::where('is_scheduled', 'YES')
                ->whereNull('finished_at')
                ->whereNull('cancel_reason')
                ->get();
        }
        else {
            $this->requests = UserRequest::where('status', strtoupper($this->active))->get();
        }
        $this->emit('clearMap');
    }

    public function loadSuggestions()
    {
        $this->suggestions = User::where('email', 'like', '%'.$this->email.'%')
            ->orWhere('first_name', 'like', '%'.$this->email.'%')
            ->orWhere('last_name', 'like', '%'.$this->email.'%')
            ->limit(5)
            ->get();
    }

    public function cancelRequest(UserRequest $userRequest)
    {
        $credentials['status'] = 'CANCELLED';
        $credentials['cancel_reason'] = 'Cancelled By Admin';
        $userRequest->update($credentials);
        $this->emit('livewire_success', 'Request Cancelled Successfully');
    }

    public function mount($active)
    {
        // $this->d_address = "Classic Bakers, Colony, Shiv Puram Phase -I, Kamaluaganja, Himmatpur Malla, Haldwani, Uttarakhand, India";
        // $this->d_latitude = 29.2135029;
        // $this->d_longitude = 79.4767016;
        // $this->distance = 6.066;
        // $this->provider_id = null;
        // $this->s_address = "Haldwani Bus Station, Bus Stand Road, Banbhoolpura, Haldwani, Uttarakhand, India";
        // $this->s_latitude = 29.2166074;
        // $this->s_longitude = 79.5302435;
        // $this->service_type_id = 1;

        if(in_array($active, ['searching', 'cancelled', 'scheduled', 'add']))
        $this->active = $active;
    }

    public function render()
    {
        $admin = auth()->user('admin');
        $lang = $admin->language ?: config('app.fallback_locale');
        app()->setLocale($lang);
        $this->services = ServiceType::all();
        $this->service_type_id = $this->services->first()->id;

        if($this->active != 'add') {
            $this->getRequests();
        }
        else {
            if(!empty($this->email)) {
                $this->loadSuggestions();
            }
        }
        
        if($this->active == 'assign_provider') {
            return view('livewire.dispatcher-dashboard', [
                'available_providers' => $this->available_providers
            ]);
        }
        else {
            return view('livewire.dispatcher-dashboard');
        }
    }
}
