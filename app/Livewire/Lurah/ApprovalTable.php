<?php

namespace App\Livewire\Lurah;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.lurah')]
#[Title('Approval Bantuan')]
class ApprovalTable extends Component
{
    public function render()
    {
        return view('livewire.lurah.approval-table');
    }
}
