<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ServiceRentalHourPackage;

class SingleServiceRental extends Component
{
    public $package;
    public $hour;
    public $km;
    public $price;
    public $show;

    protected $rules = [
        'hour' => 'required|integer|min:1',
        'km' => 'required|integer|min:1',
        'price' => 'required|integer|min:1',
    ];

    public function mount($package)
    {
        $this->package = $package;
        $this->hour = $package->hour;
        $this->km = $package->km;
        $this->price = $package->price;
        $this->show = true;
    }

    public function updateServiceRentalPackage()
    {
        $credentials = $this->validate();
        $this->package->update($credentials);
        $this->emit('livewire_success', 'Rental Package Updated Successfully.');
    }

    public function deleteServiceRentalPackage()
    {
        if(!ServiceRentalHourPackage::where('id', $this->package->id)->first()) {
            $this->emit('livewire_error', 'Rental Package Does Not Exist.');
        }
        else {
            $this->package->delete();
            $this->show = false;
            $this->emit('updatedServiceRentalList');
            $this->emit('livewire_success', 'Rental Package Deleted Successfully.');
        }
    }

    public function render()
    {
        return view('livewire.single-service-rental');
    }
}
