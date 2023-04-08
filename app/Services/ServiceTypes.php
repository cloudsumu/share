<?php

namespace App\Services;

use App\Models\GeoFencing;
use Auth;
use Exception;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\PeakHour;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\ProviderService;
use App\Models\ServicePeakHour;
use App\Models\ServiceRentalHourPackage;

class ServiceTypes
{
    public function __construct()
    {
    }

    /**
        * Get a validator for a tradepost.
        *
        * @param  array $data
        * @return \Illuminate\Contracts\Validation\Validator
    */
    protected function validator(array $data)
    {
        $rules = [
            'location'  => 'required',
        ];

        $messages = [
            'location.required' => 'Location Required!',
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function calculateFare($request, $cflag=0)
    {
        try {
            $non_geo_price = 0;
            $total   =$tax_price   ='';
            $location=$this->getLocationDistance($request);
            if (($location['data'] != 'SUCCESS')) {
                throw new Exception($location['errors']);
            } else {
                if (config('constants.distance', 'Kms') == 'Kms') {
                    $total_kilometer = round($location['meter'] / 1000, 1);
                } //TKM
                else {
                    $total_kilometer = round($location['meter'] / 1609.344, 1);
                } //TMi
                
                $requestarr['meter']       =$total_kilometer;
                $requestarr['time']        =$location['time'];
                $requestarr['seconds']     =$location['seconds'];
                $requestarr['kilometer']   =0;
                $requestarr['minutes']     =0;
                $requestarr['service_type']=$request['service_type'];
                $requestarr['s_latitude']=$request['s_latitude'];
                $requestarr['s_longitude']=$request['s_longitude'];
                $requestarr['d_latitude']=$request['d_latitude'];
                $requestarr['d_longitude']=$request['d_longitude'];
                $requestarr['service_required']= $request['service_required'] ?? 'none';
                $requestarr['rental_hours']= $request['rental_hours'] ?? 0;

                $requestarr['leave']= $request['leave'] ?? 0;
                $requestarr['return']= $request['return'] ?? 0;
                $requestarr['day']= $request['day'] ?? 0;

                $tax_percentage        = config('constants.tax_percentage');
                $commission_percentage = config('constants.commission_percentage');
                $surge_trigger         = config('constants.surge_trigger');

                $price_response=$this->applyPriceLogic($requestarr);
                if ((!empty($price_response['errors']))) {
                    throw new Exception($price_response['errors']);
                }
                
                if ($tax_percentage > 0) {
                    $tax_price        = $this->applyPercentage($price_response['price'], $tax_percentage);
                    $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                    $total            = $price_response['price'] + $tax_price;
                } else {
                    $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                    $total            = $price_response['price'];
                }
                $ActiveProviders = ProviderService::AvailableServiceProvider($request['service_type'])->get()->pluck('provider_id');

                $distance  = config('constants.provider_search_radius', '10');

                $latitude  = $request['s_latitude'];
                $longitude = $request['s_longitude'];

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
                $service_type_id =$request['service_type'];
                $geo_fencing_id  =$this->poly_check_new((round($request['s_latitude'], 6)), (round($request['s_longitude'], 6)));

                if ($geo_fencing_id != 0) {
                    $geo_fencing_service_type = GeoFencing::with(
                        ['service_geo_fencing' => function ($query) use ($service_type_id) {
                        $query->where('service_type_id', $service_type_id);
                        $query->where('status', 1);
                    }]
                    )->whereid($geo_fencing_id)->first();

                    $D_geo_fencing_id=$this->poly_check_new((round($request['d_latitude'], 6)), (round($request['d_longitude'], 6)));

                    // if(!empty($geo_fencing_service_type->service_geo_fencing->city_limits) && $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
                    if ($D_geo_fencing_id == 0) {
                        if ($requestarr['service_required'] == 'outstation') {
                            $city_limits = 1;
                        } elseif ($requestarr['service_required'] == 'none') {
                            $city_limits = 1;
                        }
                    } else {
                        if ($requestarr['service_required'] == 'outstation') {
                            // if ($request['day'] == 'round') {
                            //     \Log::alert("inside rounddd");
                            //     $total_kilometer          = $total_kilometer + $total_kilometer;
                            // }else{
                            //     $total_kilometer          =floatval($total_kilometer);
                            // }
                            $kilometer          =floatval($total_kilometer);
                            $outstation_base_km =floatval(config('constants.outstation_base_km', '0'));
                            if ($kilometer < $outstation_base_km) {
                                $city_limits = 1;
                            }
                        }
                    }

                    $check = $this->poly_check_request((round($request['d_latitude'], 6)), (round($request['d_longitude'], 6)));

                    if ($check == 'no') {
                        $non_geo_price = $geo_fencing_service_type->service_geo_fencing->non_geo_price;
                    }
                }

                $rental_hour_package = ServiceRentalHourPackage::whereservice_type_id($request['service_type'])->get();

                $rental_package = [];
                if ($requestarr['service_required'] == 'rental') {
                    if (!empty($request['rental_hours'])) {
                        $rental_package = ServiceRentalHourPackage::findOrFail($request['rental_hours']);
                    } else {
                        $rental_package = '';
                    }
                    $total = $rental_package->price;
                }
                $surge_percentage = 1 + (config('constants.surge_percentage') / 100) . 'X';

              
        


                if ($cflag != 0) {
                    if ($commission_percentage > 0) {
                        $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                        $commission_price = $price_response['price'];
                    }

                    $surge = 0;


                    $start_time = Carbon::now()->toTimeString();

                    $start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();

                    $surge_percentage = 1 + (0 / 100) . 'X';

                    if ($start_time_check) {
                        $Peakcharges = ServicePeakHour::where('service_type_id', $request['service_type'])->where('peak_hours_id', $start_time_check->id)->first();

                        if ($Peakcharges) {
                            $surge_price=($Peakcharges->min_price / 100) * $total;
                            $total += $surge_price;
                            $surge            = 1;
                            $surge_percentage = 1 + ($Peakcharges->min_price / 100) . 'X';
                        }
                    }
                }
                //\Log::alert("here");
                $return_data['estimated_fare']=$this->applyNumberFormat(floatval($total));

                if ($requestarr['service_required'] == 'outstation') {
                    if ($request['day'] == 'round') {
                        \Log::alert("inside rounddd");
                        $return_data['distance']      =      $total_kilometer + $total_kilometer;
                    }else{
                        $return_data['distance']  = floatval($total_kilometer);
                    }
                    
                }
                else
                $return_data['distance']      =$total_kilometer;

                $return_data['time']          =$location['time'];
                $return_data['minute']              = $price_response['minutes'];
                $return_data['price']              = $price_response['per_price'];
                $return_data['tax_price']     =$this->applyNumberFormat(floatval($tax_price));
                $return_data['base_price']    =$this->applyNumberFormat(floatval($price_response['base_price']));
                $return_data['service_type']  =(int)$request['service_type'];
                $return_data['service']       =$price_response['service_type'];

                if (Auth::user()) {
                    $return_data['surge']         =$surge;
                    $return_data['surge_value']   =$surge_percentage;
                    $return_data['wallet_balance']=$this->applyNumberFormat(floatval(Auth::user()->wallet_balance));
                }
                $return_data['city_limits']         = $city_limits;
                $return_data['service_required']    = $requestarr['service_required'];
                $return_data['rental_hours']        = $price_response['package_hour'] ?? 0;
                $return_data['leave']               = $price_response['leave'] ?? 0;
                $return_data['return']              = $price_response['return'] ?? 0;
                $return_data['day']                 = $price_response['day'] ?? 0;
                $return_data['limit_message']       = config('constants.limit_message', 'text here');
                $return_data['non_geo_price']       = $non_geo_price;
                $return_data['rental_hour_package'] = $rental_hour_package;
                $return_data['time_package']        = $rental_package;
                $return_data['rental_package']      = $rental_package;

                $service_response['data']=$return_data;
            }
        } catch (Exception $e) {
           $service_response['errors']=$e->getMessage().$e;
        }

        return $service_response;
    }

    public function applyPriceLogic($requestarr, $iflag=0)
    {
        $fn_response=[];
        
        \Log::alert($requestarr);
        try {
            $service_type = ServiceType::findOrFail($requestarr['service_type']);

            if ($iflag == 0) {
                //for estimated fare
            $total_kilometer = $requestarr['meter']; //TKM || TMi
            $total_minutes   = round($requestarr['seconds'] / 60); //TM
            $total_hours     =($requestarr['seconds'] / 60) / 60; //TH
            } else {
                //for invoice fare
            $total_kilometer = $requestarr['kilometer']; //TKM || TMi
            $total_minutes   = $requestarr['minutes']; //TM
            $total_hours     = $requestarr['minutes'] / 60; //TH
            }
            //$kilometer = number_format(($meter / 1000), 1);
            $kilometer = $total_kilometer ?? 0;
            $minutes   = round($total_minutes);
            //return $kilometer;
            $rental_hour = round($minutes / 60);
            $rental      = ceil($rental_hour);
            $package_hour = [];
            if (($requestarr['rental_hours']?? 0) != 0) {
                $package = ServiceRentalHourPackage::where('id', $requestarr['rental_hours'])->first();
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

            $fixed_price_only = ServiceType::findOrFail($requestarr['service_type']);
            //return $requestarr['all'];
            $geo_fencing=$this->poly_check_new((round($requestarr['s_latitude'], 6)), (round($requestarr['s_longitude'], 6)));
            //return $geo_fencing;
            if ($geo_fencing) {
                $service_type_id          = $requestarr['service_type'];
                $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' => function ($query) use ($service_type_id) {
                    $query->where('service_type_id', $service_type_id);
                    $query->where('status', 1);
                }]
                )->whereid($geo_fencing)->first();
                \Log::alert($geo_fencing_service_type);
                $service_type = $geo_fencing_service_type->service_geo_fencing;
                //\Log::alert($geo_fencing_service_type);
                if (empty($service_type)) {
                    throw new Exception(trans('api.ride.no_service_in_area'));
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
                    $timeprice = ServicePeakHour::where('service_type_id', $requestarr['service_type'])->where('peak_hours_id', $time_check_start->id)->first();

                    if ($timeprice) {
                        $time_charge = $minutes * $timeprice->peak_price;
                    }
                }

                $total_peak_minute_and_non_peak_charge = $time_charge;

            //////// -----------------Peak Time Calculation ------------ /////////
            } else {
                //throw new Exception(trans('api.ride.no_service_in_area'));
                $fixed_price_only = ServiceType::findOrFail($requestarr['service_type']);
            }

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

            if ($requestarr['service_required'] == 'rental') {
                $package = ServiceRentalHourPackage::where('id', $requestarr['rental_hours'])->first();
                $price   = $package->price;
            } elseif ($requestarr['service_required'] == 'outstation') {
                $begin = \carbon\carbon::parse($requestarr['leave']);
                

                
                $total_days = 1;
                //dd($total_days);
                $leave  = $requestarr['leave'];
                $return = $requestarr['return'];
                $day    = $requestarr['day'];

                if ($day == 'round') {
                    $end   = \carbon\carbon::parse($requestarr['return']);
                    $total_days +=  $end->diff($begin)->format('%a') + 1 ;
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
                    // \Log::alert($kilometer);
                    // \Log::alert($fixed_price_only->roundtrip_km);
                    
                    // \Log::alert($price);
                    // \Log::alert($outstation_base_km);
                    // \Log::alert($total_days);
                    // \Log::alert($requestarr);
                    // \Log::alert("inside round");
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
            }else{
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
            if ($requestarr['service_required'] == 'none') {
                if ($kilometer >= $service_type->city_limits) {
                    throw new Exception('Please book Outstation ride distance greater than ' . $service_type->city_limits . 'Km.');
                }
            }
            //return $fixed_price_only;
            }
            if (!empty($time_check_start)) {
                $timeprice = ServicePeakHour::where('peak_hours_id', $time_check_start->id)->first();

                if (!empty($timeprice)) {
                    $price += $timeprice->peak_price * $minutes;
                }
            }
            $base_distance=$fixed_price_only->distance; //BD
            $fn_response['price']     =$price;
            $fn_response['per_price']     =$service_type->price ?? 0;
            $fn_response['base_price']=$fixed_price_only->fixed;
            if ($base_distance > $total_kilometer) {
                $fn_response['distance_fare']=0;
            } else {
                $fn_response['distance_fare']=($total_kilometer - $kilometer) * $service_type->price ;
            }
            $fn_response['minute_fare'] =$total_minutes * $service_type->per_minute;
            $fn_response['hour_fare']   =$total_hours * $service_type->per_hour;
            $fn_response['calculator']  =$fixed_price_only->calculator;
            $fn_response['rental_hours']        = $package_hour ?? [];
            $fn_response['minutes']             = $total_minutes ?? 0;
            $fn_response['leave']               = $leave ?? 0;
            $fn_response['return']              = $return ?? 0;
            $fn_response['day']                 = $day ?? 0;
            $fn_response['service_type']=$fixed_price_only;


        }catch (Exception $e) {
            $fn_response['errors']=$e->getMessage().$e;
        }

        return $fn_response;
    }

    public function applyPercentage($total, $percentage)
    {
        return ($percentage / 100) * $total;
    }

    public function applyNumberFormat($total)
    {
        return $total;
//        return round($total,config('constants.round_decimal'));
    }

    public function getLocationDistance($locationarr)
    {
        $fn_response=['data'=>null, 'errors'=>null];

        try {
            $s_latitude  = $locationarr['s_latitude'];
            $s_longitude = $locationarr['s_longitude'];
            $d_latitude  = empty($locationarr['d_latitude']) ? $locationarr['s_latitude'] : $locationarr['d_latitude'];
            $d_longitude = empty($locationarr['d_longitude']) ? $locationarr['s_longitude'] : $locationarr['d_longitude'];

            if (($locationarr['service_required'] ?? 'none') == 'rental') {
                $apiurl = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $s_latitude . ',' . $s_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            } else {
                $apiurl = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&key=' . config('constants.server_map_key');
            }

            $client   = new Client();
            $location = $client->get($apiurl);
            $location = json_decode($location->getBody(), true);
            if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                throw new Exception('Out of service area', 1);
            }
            $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'] ?? 0;
            $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'] ?? 0;
            $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'] ?? 0;
            $fn_response['data']='SUCCESS';
        } catch (Exception $e) {
            $fn_response['errors']=trans('user.maperror').$e;
        }

        return $fn_response;
    }

    public function poly_check_new($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //dd($range_data);

        $yes = $no =  [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;
        if (count($range_data) != 0) {
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
        //Log::alert($range_data);

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
