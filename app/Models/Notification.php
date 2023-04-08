<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Searchable;

class Notification extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expiry_date' => 'datetime:Y-m-d',
    ];
}
