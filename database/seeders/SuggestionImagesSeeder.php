<?php

namespace Database\Seeders;

use App\Models\Suggestion;
use Illuminate\Database\Seeder;

class SuggestionImagesSeeder extends Seeder
{
    /**
     * Copy images from each source suggestion to its translation when the
     * translation has no images yet. Safe to run multiple times.
     */
    public function run(): void
    {
        $translations = Suggestion::whereNotNull('translation_of')
            ->doesntHave('images')
            ->with('translationSource.images')
            ->get();

        $copied = 0;

        foreach ($translations as $translation) {
            $source = $translation->translationSource;

            if (! $source || $source->images->isEmpty()) {
                continue;
            }

            $translation->copyImagesFrom($source);
            $copied++;

            $this->command->line("  Copied images: [{$source->locale}] {$source->title} → [{$translation->locale}] {$translation->title}");
        }

        $this->command->info("Done. Copied images for {$copied} suggestion(s).");
    }
}
