<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuggestionController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::resource('suggestions', SuggestionController::class);
});

Route::middleware(['cache.headers:public;max_age=2628000;etag'])->group(function () {
    Route::view('/', 'home');
    Route::view('/dear-googlebot', 'dear-googlebot');
    Route::get('/{whatever}', function ($whatever) {
        return view('home', compact('whatever'));
    });
});
