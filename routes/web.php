<?php

use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::post('suggestions/{suggestion}/translate', [SuggestionController::class, 'translate'])->name('suggestions.translate');
    Route::get('suggestions/export-untranslated', [SuggestionController::class, 'exportUntranslated'])->name('suggestions.export-untranslated');
    Route::post('suggestions/import-translations', [SuggestionController::class, 'importTranslations'])->name('suggestions.import-translations');
    Route::resource('suggestions', SuggestionController::class);
});

Route::redirect('/', '/'.config('app.locale'));
Route::redirect('/dear-googlebot', '/'.config('app.locale').'/dear-googlebot');

Route::prefix('{locale}')
    ->where(['locale' => 'it|en'])
    ->middleware(['cache.headers:public;max_age=2628000;etag'])
    ->group(function () {
        Route::get('/', fn (string $locale) => view('home'))->name('locale.home');
        Route::get('/dear-googlebot', fn (string $locale) => view('dear-googlebot'))->name('locale.dear-googlebot');
        Route::get('/{whatever}', fn (string $locale, string $whatever) => view('home', compact('whatever')));
    });
