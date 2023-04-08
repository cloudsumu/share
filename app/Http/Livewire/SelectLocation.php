<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class SelectLocation extends Component
{
    public $countries;
    public $states;
    public $cities;

    public $selectedCountry;
    public $selectedState;
    public $selectedCity;

    public function getStates() {
        $this->states = State::where('country_id', $this->selectedCountry)->get();
    }

    public function getCities() {
        $this->cities = City::where('state_id', $this->selectedState)->get();
    }

    // Emits an event which let's other livewire components to access this variables values.
    public function saveCity() {
        $this->emitUp('citySelected', $this->selectedCity);
    }

    public function mount($selectedCountry = NULL, $selectedCity = NULL, $selectedState = NULL) {
        // 
    }

    public function render()
    {
        $this->countries = Country::all();
        $this->getStates();
        $this->getCities();
        return view('livewire.select-location');
    }
}
