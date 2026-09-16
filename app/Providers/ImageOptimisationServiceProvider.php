<?php

namespace App\Providers;

use App\Contracts\ImageOptimisationContract;
use App\Services\ImageOptimisation;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class ImageOptimisationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ImageOptimisationContract::class,
            // Resolve by Intervention's FQCN, not the 'image' string key: since
            // Laravel 13.x ships its own Illuminate\Image\ImageManager under that
            // same 'image' alias, and whichever provider registers last wins it.
            fn ($app): ImageOptimisation => new ImageOptimisation($app->make(ImageManager::class))
        );
    }

    /**
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return [ImageOptimisationContract::class];
    }
}
