<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRentalHourPackage extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function serviceType()
    {
        $this->belongsTo(ServiceType::class);
    }
}
