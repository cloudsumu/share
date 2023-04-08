<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\GeoFencing;

class IndexSearch extends Component
{
    public $geoFences;
    public $currentFence;
    public $services;

    public function loadServices()
    {
        $this->emit('serviceListUpdated');
        
        $currentFence = GeoFencing::where('id', $this->currentFence)->first();
        $this->services = $currentFence->serviceType()->wherePivot('status', '1')->get();
    }

    public function mount($geoFences)
    {
        $this->geoFences = $geoFences;
        $this->currentFence = (int)$this->geoFences->first()->id;
        $this->loadServices();
    }

    public function render()
    {
        return view('livewire.index-search');
    }
}