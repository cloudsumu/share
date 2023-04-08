<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    use HasFactory, HasApiTokens, Searchable;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Get the Disputes of this type.
     */
    public function provider()
    {
        return $this->hasMany(Provider::class);
    }
    
    public function walletRequest()
    {
        return $this->morphMany(WalletRequest::class, 'walletRequester');
    }
}
