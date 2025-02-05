<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public $page_name;
    public $whatever;

    /**
     * Create the component instance.
     *
     * @param  string  $pageName
     * @return void
     */
    public function __construct(string $pageName = 'home', $whatever = null)
    {
        $this->page_name = $pageName;
        $this->whatever = $whatever;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
