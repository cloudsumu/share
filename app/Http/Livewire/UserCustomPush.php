<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UserCustomPush extends Component
{
    public $user_condition;
    public $condition_data;
    public $user_active;
    public $user_location;
    public $user_rides;

    public function mount($user_condition = NULL, $condition_data = NULL)
    {
        $this->user_condition = $user_condition ? $user_condition : 'ACTIVE';
        if($user_condition == 'ACTIVE')
            $this->condition_data = $condition_data ? $condition_data : $user_condition ?? 'HOUR';
        elseif($user_condition == 'RIDES')
            $this->condition_data = $condition_data ? $condition_data : $user_condition ?? '';
        elseif($user_condition == 'LOCATION') {
            $this->user_rides = $condition_data ? $condition_data : $user_condition ?? '';
        }
    }

    public function render()
    {
        if($this->user_condition == 'LOCATION') {
            $this->emit('loadSearchBox');
        }
        return view('livewire.user-custom-push');
    }
}
