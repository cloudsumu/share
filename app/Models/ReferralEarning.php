<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralEarning extends Model
{
    use HasFactory;
    protected $table='referral_earnings';

    /**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
    protected $fillable = [
        'referrer_id',        
        'type',        
        'amount',
        'count', 
        'referral_histroy_id',       
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at'
    ];
}
