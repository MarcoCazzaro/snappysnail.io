<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Database\Seeders\SuggestionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SuggestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suggestions = Suggestion::whereNull('translation_of')
            ->with('translations')
            ->get();

        return view('admin.suggestions.index', compact('suggestions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.suggestions.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'keywords' => 'required',
            'locale' => 'required',
            'sorting' => 'required',
            'url' => 'string|nullable|url',
        ]);

        $suggestion = Suggestion::create($request->all());

        $suggestion->syncImages($request);

        return redirect()->route('suggestions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Suggestion $suggestion)
    {
        return view('admin.suggestions.show', compact('suggestion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Suggestion $suggestion)
    {
        return view('admin.suggestions.edit', compact('suggestion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suggestion $suggestion)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'keywords' => 'required',
            'locale' => 'required',
            'sorting' => 'required',
            'url' => 'string|nullable|url',
        ]);

        $suggestion->update($request->all());

        $suggestion->syncImages($request);

        return redirect()->route('suggestions.index');
    }

    /**
     * Duplicate the suggestion for translation into the other locale.
     */
    public function translate(Suggestion $suggestion): RedirectResponse
    {
        $targetLocale = $suggestion->locale === 'en' ? 'it' : 'en';

        $translated = $suggestion->replicate();
        $translated->locale = $targetLocale;
        $translated->translation_of = $suggestion->id;
        $translated->save();

        return redirect()->route('suggestions.edit', $translated);
    }

    /**
     * Run SuggestionsSeeder and flash its console output back to the index view.
     */
    public function runSeeder(): RedirectResponse
    {
        Artisan::call('db:seed', [
            '--class' => SuggestionsSeeder::class,
            '--force' => true,
        ]);

        return redirect()->route('suggestions.index')->with('seederOutput', Artisan::output());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suggestion $suggestion)
    {
        $suggestion->delete();

        return redirect()->route('suggestions.index');
    }
}
