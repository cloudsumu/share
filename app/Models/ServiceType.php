<?php

namespace App\Models;

use App\Models\Agent;
use App\Models\GeoFencing;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model
{
    use HasFactory, Searchable, HasTranslations;
    use SoftDeletes;

    protected $guarded   = [];
    public $translatable = ['name', 'description'];

    public function gatNameAttribute()
    {
        return $this->name;
    }

    public function GeoFencing()
    {
        return $this->belongsToMany(GeoFencing::class, 'service_type_geo_fencings');
    }

    
    public function fleet()
    {
        return $this->belongsTo(Agent::class);
    }

   
    public function service_geo_fencing()
    {
        return $this->hasMany('App\Models\ServiceTypeGeoFencing','service_type_id');
    }
    /**
     * The services that hasmany to the service type geo fencing.
     */
    public function rental_hour_package()
    {
        return $this->hasMany('App\Models\ServiceRentalHourPackage','service_type_id');
    }
}
