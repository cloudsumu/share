<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\Searchable;

class GeoFencing extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $guarded = [];

    public function serviceType()
    {
        return $this->belongsToMany(ServiceType::class, 'service_type_geo_fencings');
    }
        /**
     * ServiceType Model Linked
     */
    public function service_geo_fencing()
    {
        return $this->hasOne('App\Models\ServiceTypeGeoFencing', 'geo_fencing_id');
    }
}
