<?php

namespace App\Models;

use App\Models\Agent;
use App\Models\ServiceType;
use App\Models\ProviderDocument;
use App\Models\Scopes\Searchable;
use App\Models\UserRequestRating;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ProviderResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Provider extends Authenticatable
{
    use HasFactory, HasApiTokens, Searchable,Notifiable, SoftDeletes;

    protected $guarded = [];

    /**
     * @var array
     */
    protected $appends = ['rating'];

    /**
     * Get the agent for given provider.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the documents of this provider.
     */
    public function documents()
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function services()
    {
        return $this->belongsToMany(ServiceType::class, 'provider_services', 'provider_id', 'service_type_id');
    }

    /**
     * Returns the Name of the User.
     *
     * @return String
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

        /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->device->token;
    }

    /**
 * Get the user's full name.
 *
 * @return string
 */
    public function getRatingAttribute()
    {
        return $this->hasMany(UserRequestRating::class)->avg('user_rating') ?? 5;
    }

    /**
     * The services that belong to the user.
     */
    public function service()
    {
        return $this->hasMany('App\Models\ProviderService');
    }

        /**
     * The services that belong to the user.
     */
    public function viewservice()
    {
        return $this->hasOne('App\Models\ProviderService');
    }

    /**
     * The services that belong to the user.
     */
    public function incoming_requests()
    {
        return $this->hasMany('App\Models\RequestFilter')->where('status', 0);
    }

    /**
     * The services that belong to the user.
     */
    public function requests()
    {
        return $this->hasMany('App\Models\RequestFilter');
    }

    /**
     * The services that belong to the user.
     */
    public function profile()
    {
        return $this->hasOne('App\Models\ProviderProfile');
    }

    /**
     * The services that belong to the user.
     */
    public function device()
    {
        return $this->hasOne('App\Models\ProviderDevice');
    }

    /**
     * The services that belong to the user.
     */
    public function trips()
    {
        return $this->hasMany('App\Models\UserRequest');
    }

    /**
     * The services accepted by the provider
     */
    public function accepted()
    {
        return $this->hasMany('App\Models\UserRequest', 'provider_id')
                    ->where('status', '!=', 'CANCELLED');
    }

    /**
     * service cancelled by provider.
     */
    public function cancelled()
    {
        return $this->hasMany('App\Models\UserRequest', 'provider_id')
                ->where('status', 'CANCELLED');
    }

    // /**
    //  * Send the password reset notification.
    //  *
    //  * @param  string  $token
    //  * @return void
    //  */
    // public function sendPasswordResetNotification($token)
    // {
    //     $this->notify(new ProviderResetPassword($token));
    // }

        /**
     * The services that belong to the user.
     */
    public function pending_documents()
    {
        return $this->hasMany('App\Models\ProviderDocument')->where('status', 'ASSESSING')->count();
    }

    public function active_documents()
    {
        return $this->hasMany('App\Models\ProviderDocument')->where('status', 'ACTIVE')->count();
    }
    public function total_requests()
    {
        return $this->hasMany('App\Models\UserRequest','provider_id')->count();
    }

    public function accepted_requests()
    {
        return $this->hasMany('App\Models\UserRequest','provider_id')->where('status','!=','CANCELLED')->count();
    }
}
