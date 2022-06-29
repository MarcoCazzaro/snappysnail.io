<?php

namespace App\View\Components;

use Illuminate\View\Component;

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
    public function __construct(string $pageName, $whatever = null)
    {
        $this->page_name = $pageName;
        $this->whatever = $whatever;
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.guest');
    }
}
