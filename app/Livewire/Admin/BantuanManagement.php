<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class BantuanManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.'.strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst('BantuanManagement'))));
    }
}
