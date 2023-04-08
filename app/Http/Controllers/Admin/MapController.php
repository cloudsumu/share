<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Provider;
use App\Models\UserRequest;
use App\Http\Controllers\Controller;

class MapController extends Controller
{
    public function index()
    {
        $active_providers = UserRequest::select('provider_id', 's_latitude', 's_longitude')->whereIn('status', ['ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP'])->whereNotNull('s_latitude')->get();
        $active_provider_ids = json_decode($active_providers->pluck('provider_id')->toJSON(), true);
        $users = User::whereNotNull('latitude')->get();
        $inactive_providers = Provider::select('latitude', 'longitude')->whereNotIn('id', $active_provider_ids)->whereNotNull('latitude')->get();

        $ref = [];
        $user = User::select('latitude', 'longitude')->whereNotNull('latitude')->first();
        $provider = Provider::select('latitude', 'longitude')->whereNotNull('latitude')->first();
        $request = UserRequest::select('s_latitude', 's_longitude')->whereNotNull('s_latitude')->first();
        if($user) {
            $ref = $user->latitude.','.$user->longitude;
        }
        else if($provider) {
            $ref = $provider->latitude.','.$provider->longitude;
        }
        else if($request) {
            $ref = $request->s_latitude.','.$request->s_longitude;
        }
        else {
            $ref = '20.5937,78.9629';
        }
        
        return view('admin.map.index', compact('inactive_providers', 'active_providers', 'users', 'ref'));
    }

    public function heatMap()
    {
        $rides = UserRequest::has('user')->orderBy('id', 'desc')->get();

        $data = [];
        $locations = [];
        foreach ($rides as $ride) {
            $locations[] = ['lat' => $ride->s_latitude, 'lng' => $ride->s_longitude];
            // $locations = $provider_location->merge($user_location);
        }
        // $locations = json_encode($locations);
        return view('admin.map.heatMap', compact('locations'));
    }
}