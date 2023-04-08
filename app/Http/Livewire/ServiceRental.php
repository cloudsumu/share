<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ServiceRentalHourPackage;

class ServiceRental extends Component
{

    public $currentService;
    public $hour;
    public $km;
    public $price;

    protected $rules = [
        'hour' => 'required|integer|min:1',
        'km' => 'required|integer|min:1',
        'price' => 'required|integer|min:1',
    ];

    protected $listeners = ['updatedServiceRentalList' => '$refresh'];

    public function mount($currentService)
    {
        $this->currentService = $currentService;
    }

    public function saveServiceRentalPackage()
    {
        $credentials = $this->validate();

        $exists = ServiceRentalHourPackage::where('hour', $credentials['hour'])
            ->where('km', $credentials['km'])
            ->where('service_type_id', $this->currentService->id)
            ->first();

        if($exists) {
            $this->emit('livewire_error', 'This Rental Package Already Exists.');
        }
        else {
            $credentials['service_type_id'] = $this->currentService->id;
            ServiceRentalHourPackage::create($credentials);
        }

        $this->hour = '';
        $this->km = '';
        $this->price = '';
        $this->emit('livewire_success', 'Rental Package Added Successfully.');
        $this->emitSelf('updatedServiceRentalList');
    }

    public function render()
    {
        return view('livewire.service-rental');
    }
}