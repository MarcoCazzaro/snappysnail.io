<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $translated->copyImagesFrom($suggestion);

        return redirect()->route('suggestions.edit', $translated);
    }

    /**
     * Download a JSON file of all suggestions that have no translation counterpart.
     */
    public function exportUntranslated(): StreamedResponse
    {
        $suggestions = Suggestion::whereNull('translation_of')
            ->doesntHave('translations')
            ->get(['id', 'title', 'keywords', 'description', 'url', 'sorting', 'locale']);

        $data = $suggestions->map(fn (Suggestion $s) => [
            'source_id' => $s->id,
            'source_locale' => $s->locale,
            'source_title' => $s->title,
            'target_locale' => $s->locale === 'en' ? 'it' : 'en',
            'title' => $s->title,
            'keywords' => $s->keywords,
            'description' => $s->description,
            'url' => $s->url,
            'sorting' => $s->sorting,
        ])->values()->all();

        return response()->streamDownload(
            fn () => print (json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'untranslated-suggestions.json',
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Import a translated JSON file and upsert suggestion translations.
     */
    public function importTranslations(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:json,txt']);

        $items = json_decode(file_get_contents($request->file('file')->path()), true);

        if (! is_array($items)) {
            return redirect()->route('suggestions.index')->with('error', __('Invalid JSON file.'));
        }

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $required = ['source_id', 'source_title', 'target_locale', 'title', 'keywords', 'description'];
            if (count(array_intersect_key(array_flip($required), $item)) !== count($required)) {
                continue;
            }

            $source = Suggestion::find($item['source_id']);

            if (! $source) {
                continue;
            }

            $data = [
                'title' => $item['title'],
                'keywords' => $item['keywords'],
                'description' => $item['description'],
                'url' => $item['url'] ?? $source->url,
                'sorting' => $item['sorting'] ?? $source->sorting,
                'locale' => $item['target_locale'],
                'translation_of' => $source->id,
            ];

            $existing = Suggestion::query()
                ->where('translation_of', $source->id)
                ->where('locale', $item['target_locale'])
                ->first();

            if ($existing) {
                $existing->update($data);
                $existing->copyImagesFrom($source);
                $updated++;
            } else {
                $newSuggestion = Suggestion::create($data);
                $newSuggestion->copyImagesFrom($source);
                $created++;
            }
        }

        return redirect()->route('suggestions.index')
            ->with('status', __('Import complete: :created created, :updated updated.', compact('created', 'updated')));
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
