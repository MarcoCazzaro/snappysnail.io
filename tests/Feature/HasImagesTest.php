<?php

use App\Models\Suggestion;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('deletes a physical file when no other image row references it', function () {
    Storage::disk('public')->put('suggestions/images/solo.webp', 'full-bytes');
    Storage::disk('public')->put('suggestions/images/solo_thumb.webp', 'thumb-bytes');

    $suggestion = Suggestion::factory()->create();
    $suggestion->images()->create([
        'file_path' => 'suggestions/images/solo.webp',
        'thumbnail_file_path' => 'suggestions/images/solo_thumb.webp',
    ]);

    $suggestion->syncImages((object) ['modelImagesIds' => '']);

    expect(Storage::disk('public')->exists('suggestions/images/solo.webp'))->toBeFalse()
        ->and(Storage::disk('public')->exists('suggestions/images/solo_thumb.webp'))->toBeFalse();
});

it('does not delete a physical file still referenced by a translation sharing it', function () {
    // Regression test: Suggestion::copyImagesFrom() gives a translation its own
    // Image row pointing at the same file as its source's row. Removing the
    // image from the source alone must not delete a file the translation
    // still needs.
    Storage::disk('public')->put('suggestions/images/shared.webp', 'full-bytes');
    Storage::disk('public')->put('suggestions/images/shared_thumb.webp', 'thumb-bytes');

    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it']);

    $source->images()->create([
        'file_path' => 'suggestions/images/shared.webp',
        'thumbnail_file_path' => 'suggestions/images/shared_thumb.webp',
    ]);
    $translation->images()->create([
        'file_path' => 'suggestions/images/shared.webp',
        'thumbnail_file_path' => 'suggestions/images/shared_thumb.webp',
    ]);

    $source->syncImages((object) ['modelImagesIds' => '']);

    expect($source->images()->count())->toBe(0)
        ->and($translation->images()->count())->toBe(1)
        ->and(Storage::disk('public')->exists('suggestions/images/shared.webp'))->toBeTrue()
        ->and(Storage::disk('public')->exists('suggestions/images/shared_thumb.webp'))->toBeTrue();
});

it('protects a shared file when removing one image among several via modelImagesIds', function () {
    Storage::disk('public')->put('suggestions/images/keep.webp', 'a');
    Storage::disk('public')->put('suggestions/images/keep_thumb.webp', 'a-thumb');
    Storage::disk('public')->put('suggestions/images/remove.webp', 'b');
    Storage::disk('public')->put('suggestions/images/remove_thumb.webp', 'b-thumb');

    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it']);

    $keepImage = $source->images()->create([
        'file_path' => 'suggestions/images/keep.webp',
        'thumbnail_file_path' => 'suggestions/images/keep_thumb.webp',
    ]);
    $source->images()->create([
        'file_path' => 'suggestions/images/remove.webp',
        'thumbnail_file_path' => 'suggestions/images/remove_thumb.webp',
    ]);
    $translation->images()->create([
        'file_path' => 'suggestions/images/remove.webp',
        'thumbnail_file_path' => 'suggestions/images/remove_thumb.webp',
    ]);

    $source->syncImages((object) ['modelImagesIds' => (string) $keepImage->id]);

    expect($source->images()->count())->toBe(1)
        ->and($translation->images()->count())->toBe(1)
        ->and(Storage::disk('public')->exists('suggestions/images/remove.webp'))->toBeTrue()
        ->and(Storage::disk('public')->exists('suggestions/images/remove_thumb.webp'))->toBeTrue();
});
