<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['cache.headers:public;max_age=2628000;etag'])->group(function () {
  	Route::view('/', 'home');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  	Route::get('/dashboard', function () {
		return view('dashboard');
	})->name('dashboard');
	Route::get('/suggestions', App\Http\Livewire\Suggestions::class)->name('suggestions');
});

Route::middleware(['cache.headers:public;max_age=2628000;etag'])->group(function () {
  	Route::get('/{whatever}', function ($whatever) {
		return view('home', compact('whatever'));
	});
});