<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRequest extends Model
{
    use HasFactory, Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i A',
        'updated_at' => 'datetime:Y-m-d H:00',
    ];

    /**
     * Set the user's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function getSAddressAttribute()
    {
        if ($this->status != 'SEARCHING') {
            return $this->attributes['s_address'] ;
        }else{
            $add = $this->attributes['s_address'] ?? "";
            return substr(strstr($add,","), 1);
            //return $this->s_address = "qweqwe";
        }
         
    }

        /**
     * Set the user's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function getDAddressAttribute()
    {
        if ($this->status != 'SEARCHING') {
            return $this->attributes['d_address'] ;
        }else{
            $add = $this->attributes['d_address'] ?? "";
            return substr(strstr($add,","), 1);
            //return $this->s_address = "qweqwe";
        }
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * ServiceType Model Linked for api
     */
    public function service_type()
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    public function geoFence()
    {
        return $this->belongsTo(GeoFencing::class);
    }

    /**
     * UserRequestPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\Models\UserRequestPayment', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function rating()
    {
        return $this->hasOne('App\Models\UserRequestRating', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function filter()
    {
        return $this->hasMany('App\Models\RequestFilter', 'request_id');
    }

    public function provider_service()
    {
        return $this->belongsTo('App\Models\ProviderService', 'provider_id', 'provider_id');
    }

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
                ->whereNotIn('status', ['CANCELLED', 'COMPLETED', 'SCHEDULED'])->with('service_type');
    }

    public function scopeRequestHistory($query)
    {
        return $query->orderBy('user_requests.created_at', 'desc')
                        ->with('user', 'payment', 'provider', 'service_type');
    }

    public function scopeUserTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.status', 'COMPLETED')
                    ->orderBy('user_requests.created_at', 'desc')
                    ->select('user_requests.*')
                    ->with('payment', 'service_type');
    }

    public function scopeUserUpcomingTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->orderBy('user_requests.created_at', 'desc')
                    ->select('user_requests.*')
                    ->with('service_type', 'provider');
    }

    public function scopeProviderUpcomingRequest($query, $user_id)
    {
        return $query->where('user_requests.provider_id', $user_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->select('user_requests.*')
                    ->with('service_type', 'user', 'provider');
    }

    public function scopeUserTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.id', $request_id)
                    ->where('user_requests.status', 'COMPLETED')
                    ->select('user_requests.*')
                    ->with('payment', 'service_type', 'user', 'provider', 'rating');
    }

    public function scopeUserUpcomingTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.id', $request_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->select('user_requests.*')
                    ->with('service_type', 'user', 'provider');
    }

    public function scopeUserRequestStatusCheck($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated', 0)
                    ->whereNotIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('user', 'provider', 'service_type', 'provider_service', 'rating', 'payment');
    }

    public function scopeUserRequestAssignProvider($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated', 0)
                    ->where('user_requests.provider_id', 0)
                    ->whereIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('filter', 'service_type');
    }

//    public function getStartedAtAttribute($value)
//    {
//        return Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y H:i:s');
//    }

//    public function getFinishedAtAttribute($value)
//    {
//        return Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y H:i:s');
//    }

   
}
