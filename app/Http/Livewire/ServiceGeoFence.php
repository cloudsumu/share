<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\PeakHour;
use Illuminate\Validation\Rule;
use App\Models\ServiceTypeGeoFencing;

class ServiceGeoFence extends Component
{
    public $gf;
    public $editing;
    public $serviceType;
    // public $fixed;
    public $price;
    public $minute;
    // public $hour;
    public $distance;
    // public $non_geo_price;
    public $city_limits;
    public $peak_hours;
    public $status;

    protected function formatStatus($format = "string")
    {
        return ($format=="string") ? (bool)$this->status : "".(int)$this->status."";
    }
    
    public function updateServiceGeoFence()
    {
        // Here the status of the parent service should also be updated, so that the parent service is visible as category to the user. If the status of parent is not enabled then the user will not be able to reach the service even thought the subService might be active.

        $this->status = $this->formatStatus();
        $credentials = $this->validate([
            // 'fixed' => 'required|',
            'price' => 'required|',
            'minute' => 'required|',
            // 'hour' => 'nullable',
            'distance' => 'required|',
            // 'non_geo_price' => 'sometimes|',
            'city_limits' => 'required|',
            'status' => [
                'sometimes',
                Rule::in([false, true]),
            ]
        ]);
        
        $credentials['status'] = $this->formatStatus('integer');
        
        $serviceTypeGeoFencing = ServiceTypeGeoFencing::where('geo_fencing_id', $this->gf->id)
            ->where('service_type_id', $this->serviceType->id)->first();
        if($serviceTypeGeoFencing) {
            $serviceTypeGeoFencing->update($credentials);
            $this->emit('livewire_success', __('crud.general.updated'));
        }
        else {
            $credentials['service_type_id'] = $this->serviceType->id;
            $credentials['geo_fencing_id'] = $this->gf->id;
            ServiceTypeGeoFencing::create($credentials);
            $this->emit('livewire_success', __('crud.general.updated'));
        }
    }

    public function mount($gf, $serviceType)
    {
        $this->gf = $gf;
        $this->serviceType = $serviceType;
        $this->peak_hours = PeakHour::all();

        $serviceTypeGeoFencing = ServiceTypeGeoFencing::where('geo_fencing_id', $this->gf->id)
            ->where('service_type_id', $this->serviceType->id)->first();
        
        $this->editing = $serviceTypeGeoFencing ? true : false;
        if($this->editing) {
            // $this->fixed = $serviceTypeGeoFencing['fixed'] = 12;
            $this->price = $serviceTypeGeoFencing['price'];
            $this->minute = $serviceTypeGeoFencing['minute'];
            $this->distance = $serviceTypeGeoFencing['distance'];
            // $this->hour = $serviceTypeGeoFencing['hour'] = 12;
            // $this->distance = $serviceTypeGeoFencing['distance'] = 12;
            // $this->non_geo_price = $serviceTypeGeoFencing['non_geo_price'] = 12;
            $this->city_limits = $serviceTypeGeoFencing['city_limits'];
            $this->status = $serviceTypeGeoFencing['status'];
            $this->status = $this->formatStatus();
        }
    }

    public function render()
    {
        return view('livewire.service-geo-fence');
    }
}
