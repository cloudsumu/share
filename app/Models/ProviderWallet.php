<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderWallet extends Model
{
    use HasFactory;

    	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
    protected $fillable = [
        'provider_id',        
        'transaction_id',        
        'transaction_alias',
        'transaction_desc',
        'type',
        'amount',
        'open_balance',
        'close_balance',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at'
    ];

        /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function transactions()
    {
        return $this->hasMany('App\Models\ProviderWallet', 'transaction_alias','transaction_alias');
    }
}
