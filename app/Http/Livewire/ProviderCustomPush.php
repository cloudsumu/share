<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ProviderCustomPush extends Component
{
    public $provider_condition;
    public $condition_data;
    public $provider_location;
    public $provider_rides;
    public $provider_active;

    public function mount($provider_condition = NULL, $condition_data = NULL)
    {
        $this->provider_condition = $provider_condition ? $provider_condition : 'ACTIVE';
        if($provider_condition == 'LOCATION')
            $this->provider_location = $condition_data ? $condition_data : $provider_condition ?? '';
        elseif($provider_condition == 'ACTIVE')
            $this->provider_active = $condition_data ? $condition_data : $provider_condition ?? 'HOUR';
        elseif($provider_condition == 'RIDES')
            $this->provider_rides = $condition_data ? $condition_data : $provider_condition ?? '';
    }

    public function render()
    {
        if($this->provider_condition == 'LOCATION') {
            $this->emit('loadSearchBox');
        }
        return view('livewire.provider-custom-push');
    }
}
