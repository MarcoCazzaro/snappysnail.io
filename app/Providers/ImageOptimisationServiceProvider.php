<?php

namespace App\Providers;

use App\Contracts\ImageOptimisationContract;
use App\Services\ImageOptimisation;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ImageOptimisationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ImageOptimisationContract::class,
            // Intervention's ImageManager is bound under the 'image' key by
            // intervention/image-laravel (Facades\Image::BINDING), not its FQCN —
            // resolving the class name directly would try to auto-build it without
            // the required $driver constructor arg and fail.
            fn ($app): ImageOptimisation => new ImageOptimisation($app->make('image'))
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
