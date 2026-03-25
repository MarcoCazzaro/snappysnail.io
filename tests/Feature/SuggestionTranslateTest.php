<?php

use App\Models\Image;
use App\Models\Suggestion;
use App\Models\User;

it('duplicates a suggestion with the opposite locale', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create(['locale' => 'en', 'title' => 'Original Title']);

    $this->actingAs($user)
        ->post(route('suggestions.translate', $suggestion))
        ->assertRedirect();

    $translated = Suggestion::where('title', 'Original Title')
        ->where('locale', 'it')
        ->first();

    expect($translated)->not->toBeNull();
    expect($translated->title)->toBe('Original Title');
    expect($translated->keywords)->toBe($suggestion->keywords);
    expect($translated->description)->toBe($suggestion->description);
});

it('sets translation_of on the translated suggestion', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->post(route('suggestions.translate', $suggestion));

    $translated = Suggestion::latest('id')->first();

    expect($translated->translation_of)->toBe($suggestion->id);
});

it('flips locale from it to en when translating', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create(['locale' => 'it']);

    $this->actingAs($user)
        ->post(route('suggestions.translate', $suggestion))
        ->assertRedirect();

    expect(Suggestion::where('locale', 'en')->where('title', $suggestion->title)->exists())->toBeTrue();
});

it('redirects to the edit page of the new translated suggestion', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create(['locale' => 'en']);

    $response = $this->actingAs($user)
        ->post(route('suggestions.translate', $suggestion));

    $translated = Suggestion::latest('id')->first();
    $response->assertRedirect(route('suggestions.edit', $translated));
});

it('copies images to the translated suggestion', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create(['locale' => 'en']);

    Image::withoutEvents(fn () => $suggestion->images()->create([
        'file_path' => 'suggestions/images/test.jpg',
        'thumbnail_file_path' => 'suggestions/images/test_thumb.jpg',
        'optimised_at' => now(),
    ]));

    $this->actingAs($user)
        ->post(route('suggestions.translate', $suggestion));

    $translated = Suggestion::latest('id')->first();

    expect($translated->images)->toHaveCount(1);
    expect($translated->images->first()->file_path)->toBe('suggestions/images/test.jpg');
});

it('requires authentication to translate a suggestion', function () {
    $suggestion = Suggestion::factory()->create();

    $this->post(route('suggestions.translate', $suggestion))
        ->assertRedirect(route('login'));
});
