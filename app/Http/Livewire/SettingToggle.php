<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SettingToggle extends Component
{
    public $value = '';
    public $field = '';

    public $isActive = false;

    public function mount($value)
    {
        if ($value == 1) {
            $this->isActive = (bool) true;
        } else {
            $this->isActive = (bool)  false;
        }
    }

    public function render()
    {
        return view('livewire.setting-toggle');
    }

    public function updating($field, $value)
    {
        return  $value;
    }
}
