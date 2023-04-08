<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ServiceType;
use App\Models\ProviderService;

class ProviderSingleService extends Component
{

    public $provider; // current provider
    public $service; // Service Type
    public $index; // loop index
    public $service_model; // vehicle model
    public $service_number; // vehicle number plate

    public $providerService; // Current Provider Service

    public function mount($service, $index, $provider)
    {
        $this->service = $service;
        $this->index = $index;
        $this->provider = $provider;
        $this->providerService = ProviderService::where('service_type_id', $this->service->id)  
            ->where('provider_id', $this->provider->id)
            ->first();
        if($this->providerService) {
            $this->service_model = $this->providerService->service_model;
            $this->service_number = $this->providerService->service_number;
        }
    }

    public function removeService()
    {
        if(!$this->providerService) {
            $this->emit('livewire_error', 'Service Does Not Exist');
            return;
        }

        $this->providerService->delete();
        $this->emitUp('refreshProviderService');
        $this->emit('livewire_success', 'Service Removed From Provider Successfully');
    }

    public function updateProviderDetails()
    {
        if(!$this->providerService) {
            $this->emit('livewire_error', 'Service Does Not Exist');
            return;
        }
        $credentials = $this->validate([
            'service_model' => 'required|',
            'service_number' => 'required|',
        ]);

        $this->providerService->update($credentials);
        $this->emit('livewire_success', 'Provider Service Details Updated Successfully.');
    }

    public function render()
    {
        return view('livewire.provider-single-service');
    }
}
