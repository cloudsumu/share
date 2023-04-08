<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ChangeLanguage extends Component
{
    public $locales;

    public function mount()
    {
        $this->locales = get_all_language();
    }
    public function render()
    {
        return view('livewire.change-language');
    }
}
