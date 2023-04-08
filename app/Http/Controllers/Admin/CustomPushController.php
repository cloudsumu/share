<?php

namespace App\Http\Controllers\Admin;

use Exception;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Provider;
use App\Models\CustomPush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SendPushNotification;
use App\Http\Requests\Admin\StoreCustomPushRequest;
use App\Http\Requests\Admin\UpdateCustomPushRequest;

class CustomPushController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['update', 'destroy']);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list customPushes', customPush::class);
        $search = $request->get('search', '');

        $customPushes = CustomPush::search($search)
            ->latest()
            ->paginate();

        return view('admin.customPush.index', compact('customPushes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create customPushes', customPush::class);
        return view('admin.customPush.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'send_to'            => 'required|in:ALL,USERS,PROVIDERS',
            'user_condition'     => ['required_if:send_to,USERS', 'in:ACTIVE,LOCATION,RIDES,AMOUNT'],
            'provider_condition' => ['required_if:send_to,PROVIDERS', 'in:ACTIVE,LOCATION,RIDES,AMOUNT'],
            'user_active'        => ['required_if:user_condition,ACTIVE', 'in:HOUR,WEEK,MONTH'],
            'user_rides'         => 'required_if:user_condition,RIDES',
            'user_location'      => 'required_if:user_condition,LOCATION',
            'user_amount'        => 'required_if:user_condition,AMOUNT',
            'provider_active'    => ['required_if:provider_condition,ACTIVE', 'in:HOUR,WEEK,MONTH'],
            'provider_rides'     => 'required_if:provider_condition,RIDES',
            'provider_location'  => 'required_if:provider_condition,LOCATION',
            'provider_amount'    => 'required_if:provider_condition,AMOUNT',
            'message'            => 'required|max:100',
        ]);

        try {
            $CustomPush          = new CustomPush();
            $CustomPush->send_to = $request->send_to;
            $CustomPush->message = $request->message;

            if ($request->send_to == 'USERS') {
                $CustomPush->condition = $request->user_condition;

                if ($request->user_condition == 'ACTIVE') {
                    $CustomPush->condition_data = $request->user_active;
                } elseif ($request->user_condition == 'LOCATION') {
                    $CustomPush->condition_data = $request->user_location;
                } elseif ($request->user_condition == 'RIDES') {
                    $CustomPush->condition_data = $request->user_rides;
                } elseif ($request->user_condition == 'AMOUNT') {
                    $CustomPush->condition_data = $request->user_amount;
                }
            } elseif ($request->send_to == 'PROVIDERS') {
                $CustomPush->condition = $request->provider_condition;

                if ($request->provider_condition == 'ACTIVE') {
                    $CustomPush->condition_data = $request->provider_active;
                } elseif ($request->provider_condition == 'LOCATION') {
                    $CustomPush->condition_data = $request->provider_location;
                } elseif ($request->provider_condition == 'RIDES') {
                    $CustomPush->condition_data = $request->provider_rides;
                } elseif ($request->provider_condition == 'AMOUNT') {
                    $CustomPush->condition_data = $request->provider_amount;
                }
            }

            if ($request->has('`schedule`_date') && $request->has('schedule_time')) {
                $CustomPush->schedule_at = date('Y-m-d H:i:s', strtotime("$request->schedule_date $request->schedule_time"));
            }

            $CustomPush->save();

            if ($CustomPush->schedule_at == '') {
                $this->SendCustomPush($CustomPush->id);
            }

            return redirect()
                ->route('admin.customPush.index')
                ->with('success', (__('crud.general.created')));
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withErrors('Something went wrong!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomPush $customPush)
    {
        abort(403);
        $this->authorize('view customPushes', $customPush);
        return view('admin.customPush.edit', compact('customPush'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomPushRequest $request, CustomPush $customPush)
    {
        abort(403);
        $credentials = $request->validated();

        $customPush->update($credentials);
        return redirect()
            ->route('admin.customPush.index')
            ->with('success', (__('crud.general.updated')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomPush $customPush)
    {
        $this->authorize('delete customPushes', $customPush);
        try {
            $customPush->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
                return redirect()
                    ->back()
                    ->withErrors(__('crud.general.integrity_violation'));
            }
            return redirect()
                ->back()
                ->withErrors(__('crud.general.not_done'));
        }

        return redirect()
            ->back()
            ->withSuccess(__('crud.general.deleted'));
    }

    public function SendCustomPush($CustomPush)
    {
        try {
            Log::notice('Starting Custom Push');

            $Push = CustomPush::findOrFail($CustomPush);

            if ($Push->send_to == 'USERS') {
                $Users = [];

                if ($Push->condition == 'ACTIVE') {
                    if ($Push->condition_data == 'HOUR') {
                        $Users = User::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subHour());
                        })->get();
                    } elseif ($Push->condition_data == 'WEEK') {
                        $Users = User::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subWeek());
                        })->get();
                    } elseif ($Push->condition_data == 'MONTH') {
                        $Users = User::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subMonth());
                        })->get();
                    }
                } elseif ($Push->condition == 'RIDES') {
                    $Users = User::whereHas('trips', function ($query) use ($Push) {
                        $query->where('status', 'COMPLETED');
                        $query->groupBy('id');
                        $query->havingRaw('COUNT(*) >= ' . $Push->condition_data);
                    })->get();
                } elseif ($Push->condition == 'LOCATION') {
                    $Location = explode(',', $Push->condition_data);

                    $distance  = config('constants.provider_search_radius', '10');
                    $latitude  = $Location[0];
                    $longitude = $Location[1];

                    $Users = User::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();
                }

                foreach ($Users as $key => $user) {
                    (new SendPushNotification())->sendPushToUser($user->id, $Push->message);
                }
            } elseif ($Push->send_to == 'PROVIDERS') {
                $Providers = [];

                if ($Push->condition == 'ACTIVE') {
                    if ($Push->condition_data == 'HOUR') {
                        $Providers = Provider::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subHour());
                        })->get();
                    } elseif ($Push->condition_data == 'WEEK') {
                        $Providers = Provider::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subWeek());
                        })->get();
                    } elseif ($Push->condition_data == 'MONTH') {
                        $Providers = Provider::whereHas('trips', function ($query) {
                            $query->where('created_at', '>=', Carbon::now()->subMonth());
                        })->get();
                    }
                } elseif ($Push->condition == 'RIDES') {
                    $Providers = Provider::whereHas('trips', function ($query) use ($Push) {
                        $query->where('status', 'COMPLETED');
                        $query->groupBy('id');
                        $query->havingRaw('COUNT(*) >= ' . $Push->condition_data);
                    })->get();
                } elseif ($Push->condition == 'LOCATION') {
                    $Location = explode(',', $Push->condition_data);

                    $distance  = config('constants.provider_search_radius', '10');
                    $latitude  = $Location[0];
                    $longitude = $Location[1];

                    $Providers = Provider::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();
                }

                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification())->sendPushToProvider($provider->id, $Push->message);
                }
            } elseif ($Push->send_to == 'ALL') {
                $Users = User::all();
                foreach ($Users as $key => $user) {
                    (new SendPushNotification())->sendPushToUser($user->id, $Push->message);
                }

                $Providers = Provider::all();
                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification())->sendPushToProvider($provider->id, $Push->message);
                }
            }
        } catch (Exception $e) {
            return back()->with('flash_error', 'Something went wrong!');
        }
    }
}