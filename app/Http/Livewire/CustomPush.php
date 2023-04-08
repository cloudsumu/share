<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CustomPush extends Component
{
    public $value;
    public $send_to;
    public $scheduled_at;
    public $condition;
    public $condition_data;

    public function mount($send_to = NULL, $condition = NULL, $condition_data = NULL, $scheduled_at = NULL)
    {
        $this->send_to = $send_to;
        $this->condition = $condition;
        $this->condition_data = $condition_data;
        $this->scheduled_at = $scheduled_at;
    }

    public function render()
    {
        return view('livewire.custom-push');
    }
}