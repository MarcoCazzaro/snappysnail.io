<?php

namespace Database\Seeders;

use App\Models\Suggestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SuggestionsSeeder extends Seeder
{
    /**
     * Seed every suggestion in every locale from the single merged data file.
     *
     * Idempotent: each (key, locale) row is upserted, so re-running applies
     * content edits without creating duplicates. Images are attached to the
     * English source only — Suggestion::images() delegates a translation to
     * its source, so nothing needs to be copied onto the translation.
     */
    public function run(): void
    {
        $groups = json_decode(File::get(database_path('seeders/data/suggestions.json')), true);

        foreach ($groups as $group) {
            $source = $this->upsertLocale($group, 'en', null);
            $this->attachImages($source, $group['images'] ?? null);

            foreach (array_keys($group['translations']) as $locale) {
                if ($locale === 'en') {
                    continue;
                }

                $this->upsertLocale($group, $locale, $source->id);
            }
        }
    }

    /**
     * @param  array{key: string, sorting?: int, url?: string|null, translations: array<string, array{title: string, keywords: string, description: string}>}  $group
     */
    private function upsertLocale(array $group, string $locale, ?int $translationOf): Suggestion
    {
        $fields = $group['translations'][$locale];

        return Suggestion::updateOrCreate(
            ['key' => $group['key'], 'locale' => $locale],
            [
                'title' => $fields['title'],
                'keywords' => $fields['keywords'],
                'description' => $this->resolveDescription($fields['description']),
                'url' => $group['url'] ?? null,
                'sorting' => $group['sorting'] ?? 0,
                'translation_of' => $translationOf,
            ],
        );
    }

    /**
     * A description of "views.some.view" is rendered from that Blade view;
     * anything else is stored verbatim.
     */
    private function resolveDescription(string $description): string
    {
        if (Str::startsWith($description, 'views.')) {
            return (string) View::make(Str::after($description, 'views.'));
        }

        return $description;
    }

    /**
     * Copy every image in database/seeders/data/images/works/{folder} onto the
     * suggestion; syncImages() optimises each one. Skipped when the suggestion
     * already has images so re-seeding does not churn storage.
     */
    private function attachImages(Suggestion $suggestion, ?string $folder): void
    {
        if ($folder === null || $suggestion->images()->exists()) {
            return;
        }

        $path = database_path('seeders/data/images/works/'.$folder);

        if (! File::isDirectory($path)) {
            return;
        }

        $paths = collect(File::files($path))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            ->map(fn ($file) => $file->getRealPath())
            ->values();

        if ($paths->isEmpty()) {
            return;
        }

        $suggestion->syncImages((object) ['tempImagesPaths' => $paths->implode(',')]);
    }
}
