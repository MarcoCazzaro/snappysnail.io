<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ServiceCard extends Component
{
    public $title;
    public $image_path;
    public $description;

    public function render()
    {
        return view('livewire.service-card');
    }
}
