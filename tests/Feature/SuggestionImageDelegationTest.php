<?php

use App\Models\Image;
use App\Models\Suggestion;

it('gives a source suggestion its own image rows', function () {
    $suggestion = Suggestion::factory()->create(['locale' => 'en']);

    $image = $suggestion->images()->create([
        'file_path' => 'suggestions/images/a.webp',
        'thumbnail_file_path' => 'suggestions/images/a_thumb.webp',
    ]);

    expect($image->imageable_id)->toBe($suggestion->id)
        ->and($image->imageable_type)->toBe(Suggestion::class);
});

it('delegates a translation\'s images relation to its source, by reference', function () {
    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it', 'translation_of' => $source->id]);

    $image = $source->images()->create([
        'file_path' => 'suggestions/images/a.webp',
        'thumbnail_file_path' => 'suggestions/images/a_thumb.webp',
    ]);

    expect($translation->images)->toHaveCount(1)
        ->and($translation->images->first()->id)->toBe($image->id);
});

it('attaches a new image created through a translation to its source, not the translation', function () {
    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it', 'translation_of' => $source->id]);

    $image = $translation->images()->create([
        'file_path' => 'suggestions/images/b.webp',
        'thumbnail_file_path' => 'suggestions/images/b_thumb.webp',
    ]);

    expect($image->imageable_id)->toBe($source->id)
        ->and(Image::where('imageable_id', $translation->id)->count())->toBe(0);
});

it('delegates latestImage() and oldestImage() to the source too', function () {
    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it', 'translation_of' => $source->id]);

    $first = $source->images()->create([
        'file_path' => 'suggestions/images/first.webp',
        'thumbnail_file_path' => 'suggestions/images/first_thumb.webp',
    ]);
    $second = $source->images()->create([
        'file_path' => 'suggestions/images/second.webp',
        'thumbnail_file_path' => 'suggestions/images/second_thumb.webp',
    ]);

    expect($translation->oldestImage()->first()->id)->toBe($first->id)
        ->and($translation->latestImage()->first()->id)->toBe($second->id);
});
