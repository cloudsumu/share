<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ServicePeakHour;

class ServiceTypePeakHour extends Component
{
    public $ph;
    public $min_price;
    public $serviceType;

    public function updateServicePeakHour()
    {
        // Send Error From This Validator To Livewire Event Listener. For Right Now It Just Prints Errors In the View Component.
        $credentials = $this->validate([
            'min_price' => 'required|',
        ]);

        $servicePeakHour = ServicePeakHour::where('peak_hours_id', $this->ph->id)
            ->where('service_type_id', $this->serviceType->id)->first();
        if ($servicePeakHour) {
            $servicePeakHour->update($credentials);
            $this->emit('livewire_success', __('crud.general.updated'));
        } else {
            $credentials['service_type_id'] = $this->serviceType->id;
            $credentials['peak_hours_id']   = $this->ph->id;
            ServicePeakHour::create($credentials);
            $this->emit('livewire_success', __('crud.general.updated'));
        }
    }

    public function mount($ph, $serviceType)
    {
        $this->ph          = $ph;
        $this->serviceType = $serviceType;
        $servicePeakHour   = ServicePeakHour::where('peak_hours_id', $this->ph->id)
            ->where('service_type_id', $this->serviceType->id)->first();
        if ($servicePeakHour) {
            $this->min_price = $servicePeakHour->min_price;
        }
    }

    public function render()
    {
        return view('livewire.service-type-peak-hour');
    }
}
