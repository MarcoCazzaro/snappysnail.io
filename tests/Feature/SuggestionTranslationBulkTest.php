<?php

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Http\UploadedFile;

// --- exportUntranslated ---

it('exports only suggestions without a translation pair', function () {
    $user = User::factory()->create();

    // Paired: same title, both locales
    Suggestion::factory()->create(['title' => 'Paired', 'locale' => 'en']);
    Suggestion::factory()->create(['title' => 'Paired', 'locale' => 'it']);

    // Unpaired: only en
    $solo = Suggestion::factory()->create(['title' => 'Solo', 'locale' => 'en']);

    $response = $this->actingAs($user)
        ->get(route('suggestions.export-untranslated'));

    $response->assertOk();

    $data = json_decode($response->streamedContent(), true);

    expect($data)->toHaveCount(1);
    expect($data[0]['source_id'])->toBe($solo->id);
    expect($data[0]['source_title'])->toBe('Solo');
    expect($data[0]['target_locale'])->toBe('it');
});

it('exports suggestions from both locales when unpaired', function () {
    $user = User::factory()->create();

    $en = Suggestion::factory()->create(['title' => 'English Only', 'locale' => 'en']);
    $it = Suggestion::factory()->create(['title' => 'Italian Only', 'locale' => 'it']);

    $response = $this->actingAs($user)
        ->get(route('suggestions.export-untranslated'));

    $data = json_decode($response->streamedContent(), true);

    $ids = array_column($data, 'source_id');
    expect($ids)->toContain($en->id);
    expect($ids)->toContain($it->id);
});

it('returns an empty array when all suggestions are paired', function () {
    $user = User::factory()->create();

    Suggestion::factory()->create(['title' => 'Matched', 'locale' => 'en']);
    Suggestion::factory()->create(['title' => 'Matched', 'locale' => 'it']);

    $response = $this->actingAs($user)
        ->get(route('suggestions.export-untranslated'));

    $data = json_decode($response->streamedContent(), true);

    expect($data)->toBeEmpty();
});

it('requires authentication to export', function () {
    $this->get(route('suggestions.export-untranslated'))
        ->assertRedirect(route('login'));
});

// --- importTranslations ---

it('imports and creates new translated suggestions', function () {
    $user = User::factory()->create();
    $source = Suggestion::factory()->create(['title' => 'Hello', 'locale' => 'en', 'sorting' => 5]);

    $json = json_encode([[
        'source_id' => $source->id,
        'source_locale' => 'en',
        'source_title' => 'Hello',
        'target_locale' => 'it',
        'title' => 'Ciao',
        'keywords' => 'parola chiave',
        'description' => '<p>Descrizione</p>',
        'url' => $source->url,
        'sorting' => $source->sorting,
    ]]);

    $file = UploadedFile::fake()->createWithContent('translations.json', $json);

    $this->actingAs($user)
        ->post(route('suggestions.import-translations'), ['file' => $file])
        ->assertRedirect(route('suggestions.index'));

    $translated = Suggestion::where('locale', 'it')->where('title', 'Ciao')->first();

    expect($translated)->not->toBeNull();
    expect($translated->keywords)->toBe('parola chiave');
    expect($translated->description)->toBe('<p>Descrizione</p>');
    expect($translated->sorting)->toBe(5);
});

it('imports and updates an existing suggestion matched by source_title', function () {
    $user = User::factory()->create();
    $source = Suggestion::factory()->create(['title' => 'Hello', 'locale' => 'en']);
    $existing = Suggestion::factory()->create(['title' => 'Hello', 'locale' => 'it', 'keywords' => 'old']);

    $json = json_encode([[
        'source_id' => $source->id,
        'source_locale' => 'en',
        'source_title' => 'Hello',
        'target_locale' => 'it',
        'title' => 'Ciao',
        'keywords' => 'nuovo',
        'description' => '<p>Aggiornato</p>',
        'url' => null,
        'sorting' => 10,
    ]]);

    $file = UploadedFile::fake()->createWithContent('translations.json', $json);

    $this->actingAs($user)
        ->post(route('suggestions.import-translations'), ['file' => $file]);

    expect(Suggestion::where('locale', 'it')->count())->toBe(1);

    $existing->refresh();
    expect($existing->keywords)->toBe('nuovo');
    expect($existing->title)->toBe('Ciao');
});

it('skips entries with missing required keys', function () {
    $user = User::factory()->create();
    $source = Suggestion::factory()->create(['title' => 'Hello', 'locale' => 'en']);

    $json = json_encode([
        // Valid entry
        [
            'source_id' => $source->id,
            'source_locale' => 'en',
            'source_title' => 'Hello',
            'target_locale' => 'it',
            'title' => 'Ciao',
            'keywords' => 'parole',
            'description' => '<p>Ok</p>',
        ],
        // Missing 'description'
        [
            'source_id' => $source->id,
            'source_title' => 'Hello',
            'target_locale' => 'it',
            'title' => 'Ciao',
            'keywords' => 'parole',
        ],
    ]);

    $file = UploadedFile::fake()->createWithContent('translations.json', $json);

    $this->actingAs($user)
        ->post(route('suggestions.import-translations'), ['file' => $file])
        ->assertRedirect(route('suggestions.index'))
        ->assertSessionHas('status');

    expect(Suggestion::where('locale', 'it')->count())->toBe(1);
});

it('rejects a non-json file upload', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('translations.pdf', 10, 'application/pdf');

    $this->actingAs($user)
        ->post(route('suggestions.import-translations'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('requires authentication to import', function () {
    $file = UploadedFile::fake()->createWithContent('translations.json', '[]');

    $this->post(route('suggestions.import-translations'), ['file' => $file])
        ->assertRedirect(route('login'));
});
