<?php

namespace App\Models;

use App\Models\UserRequest;
use App\Models\WalletRequest;
use App\Models\Scopes\Searchable;
use App\Models\UserRequestRating;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, Searchable, HasRoles;
    use SoftDeletes;

    /**
     * Contains the role of this model
     * @var string
     */
    protected $role = 'user';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->device_token;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Returns the user for this Model.
     *
     * @return String
     */
    public function getRoleAttribute()
    {
        return $this->role;
    }

    protected $appends = ['rating'];

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
     * Get the user's full name.
     *
     * @return string
    */
    public function getRatingAttribute()
    {
        return $this->hasMany(UserRequestRating::class)->avg('provider_rating') ?? 5;
    }

    public function walletRequest()
    {
        return $this->morphMany(WalletRequest::class, 'walletRequester');
    }

    public function userRequest()
    {
        return $this->hasMany(UserRequest::class, 'user_id', 'id');
    }
}
