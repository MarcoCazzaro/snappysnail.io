<?php

namespace App\Providers;

use App\View\Composers\SEOComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SEOServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        View::composer(
            ['home', 'dear-googlebot', 'policy', 'terms', 'layouts.app', 'layouts.guest'],
            SEOComposer::class
        );
    }
}
