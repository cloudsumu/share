<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promocode extends Model
{
    use SoftDeletes, HasFactory, Searchable;

    /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
    protected $fillable = [
        'agent_id', 'promo_code', 'percentage', 'max_amount', 'expiration', 'promo_description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at',
    ];

    public function promousage()
    {
        return $this->hasMany('App\Models\PromocodeUsage', 'promocode_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','expiration'];
}
