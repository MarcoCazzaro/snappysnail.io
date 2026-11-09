<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Features\SupportFileUploads\MissingFileUploadsTraitException;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->map(
            MissingFileUploadsTraitException::class,
            fn (MissingFileUploadsTraitException $e) => new BadRequestHttpException($e->getMessage(), $e),
        );

        $exceptions->dontReport([
            PublicPropertyNotFoundException::class,
            MethodNotFoundException::class,
            ComponentNotFoundException::class,
            CorruptComponentPayloadException::class,
        ]);
    })->create();
