<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Button extends Component
{
    public $url;
    public $label;
    public function render()
    {
        return <<<'blade'
            <a class="inline-flex items-center h-10 px-4 m-2 text-slate-100 transition-colors duration-150 bg-amber-500 rounded-lg focus:shadow-outline hover:bg-amber-600" href="{{ $this->url }}">{{ $this->label }}</a>
        blade;
    }
}
