<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;

class Catalogs extends Component
{
    public $selected_catalog = '';

    public function render()
    {
        return view(
            'livewire.target.catalogs'
        );
    }
}
