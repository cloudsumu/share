<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Provider;
use App\Models\ServiceType;
use App\Models\ProviderService;

class ProviderServices extends Component
{
    protected $listeners = ['refreshProviderService' => '$refresh'];

    public $search = '';
    public $results = [];
    public $provider;
    public $alreadyHave;

    public function addService(ServiceType $serviceType)
    {
        $credentials['provider_id'] = $this->provider->id;
        $credentials['service_type_id'] = $serviceType->id;
        $credentials['status'] = 'offline';
        ProviderService::create($credentials);
        $this->emit('refreshProviderService');
        $this->emit('livewire_success', 'Service Added To Provider Successfully');
    }

    public function mount(Provider $provider)
    {
        $this->provider = $provider;
        $this->alreadyHave = $provider->services->pluck('id');
    }

    public function render()
    {
        $this->alreadyHave = $this->provider->services->pluck('id');
        $this->results = ServiceType::search($this->search)->limit(5)->get();
        $this->results = ServiceType::where('name', 'like', "%$this->search%")
            ->whereNotIn('id', $this->alreadyHave)
            ->get();

        return view('livewire.provider-services');
    }
}