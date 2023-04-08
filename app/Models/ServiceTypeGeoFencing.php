<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTypeGeoFencing extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function GeoFencing()
    {
        return $this->belongsTo(GeoFencing::class);
    }
}
