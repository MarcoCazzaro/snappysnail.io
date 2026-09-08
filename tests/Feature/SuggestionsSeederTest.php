<?php

use App\Models\Image;
use App\Models\Suggestion;
use Database\Seeders\SuggestionsSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function seededSuggestions(): Builder
{
    return Suggestion::query()->withoutGlobalScopes();
}

it('seeds every suggestion in both locales with a key', function () {
    $this->seed(SuggestionsSeeder::class);

    expect(seededSuggestions()->where('locale', 'en')->count())->toBe(23)
        ->and(seededSuggestions()->where('locale', 'it')->count())->toBe(23)
        ->and(seededSuggestions()->whereNull('key')->count())->toBe(0);
});

it('links every translation to its english source by shared key', function () {
    $this->seed(SuggestionsSeeder::class);

    $translations = seededSuggestions()->where('locale', 'it')->get();

    expect($translations)->toHaveCount(23);

    $translations->each(function (Suggestion $translation) {
        $source = seededSuggestions()->find($translation->translation_of);

        expect($source)->not->toBeNull()
            ->and($source->locale)->toBe('en')
            ->and($source->key)->toBe($translation->key);
    });
});

it('is idempotent: re-seeding creates no new rows or files', function () {
    $this->seed(SuggestionsSeeder::class);

    $pairsBefore = seededSuggestions()->orderBy('id')->pluck('id')->all();
    $imagesBefore = Image::count();

    $this->seed(SuggestionsSeeder::class);

    expect(seededSuggestions()->orderBy('id')->pluck('id')->all())->toBe($pairsBefore)
        ->and(Image::count())->toBe($imagesBefore)
        ->and(seededSuggestions()->count())->toBe(46);
});

it('upserts content edits onto the existing row instead of duplicating', function () {
    $this->seed(SuggestionsSeeder::class);

    seededSuggestions()->where(['key' => 'bertone-design', 'locale' => 'en'])
        ->update(['title' => 'Stale title', 'sorting' => 1]);

    $this->seed(SuggestionsSeeder::class);

    $row = seededSuggestions()->where(['key' => 'bertone-design', 'locale' => 'en'])->get();

    expect($row)->toHaveCount(1)
        ->and($row->first()->title)->toBe('Bertone Design')
        ->and($row->first()->sorting)->toBe(202501);
});

it('renders a "views." description reference and delegates the translation\'s images to its source', function () {
    $this->seed(SuggestionsSeeder::class);

    $contact = seededSuggestions()->where(['key' => 'contact', 'locale' => 'en'])->firstOrFail();
    expect($contact->description)->not->toStartWith('views.')
        ->and($contact->description)->toContain('<');

    $en = seededSuggestions()->where(['key' => 'bertone-design', 'locale' => 'en'])->firstOrFail();
    $it = seededSuggestions()->where(['key' => 'bertone-design', 'locale' => 'it'])->firstOrFail();

    expect($en->images()->count())->toBeGreaterThan(0)
        ->and($it->images()->pluck('id')->sort()->values()->all())
        ->toBe($en->images()->pluck('id')->sort()->values()->all());
});
