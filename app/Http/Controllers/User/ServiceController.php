<?php

namespace App\Http\Controllers\User;

use App\Models\GeoFencing;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function serviceCheckout(Request $request, GeoFencing $geoFence, ServiceType $serviceType)
    {
        // 
        return view('user.serviceCheckout');
    }
}
