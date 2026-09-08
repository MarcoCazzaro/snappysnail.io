<?php

use App\Models\Image;
use App\Models\Suggestion;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

beforeEach(function () {
    Storage::fake('public');
});

function putRawImage(int $width, int $height, string $relativePath): void
{
    $tmp = tempnam(sys_get_temp_dir(), 'ssnail-raw-').'.png';
    ImageManager::gd()->create($width, $height)->fill('00ff00')->save($tmp);

    Storage::disk('public')->put($relativePath, file_get_contents($tmp));
    unlink($tmp);
}

it('reprocesses a legacy raw image, replacing its file paths and deleting the old files', function () {
    $suggestion = Suggestion::factory()->create();
    putRawImage(1200, 800, 'suggestions/images/legacy.jpg');

    $image = $suggestion->images()->create([
        'file_path' => 'suggestions/images/legacy.jpg',
        'thumbnail_file_path' => 'suggestions/images/legacy.jpg',
    ]);

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    $image->refresh();

    expect($image->file_path)->not->toBe('suggestions/images/legacy.jpg')
        ->and($image->thumbnail_file_path)->not->toBe('suggestions/images/legacy.jpg')
        ->and($image->thumbnail_file_path)->not->toBe($image->file_path)
        ->and(Storage::disk('public')->exists($image->file_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($image->thumbnail_file_path))->toBeTrue()
        ->and(Storage::disk('public')->exists('suggestions/images/legacy.jpg'))->toBeFalse();

    $thumbnailDimensions = getimagesize(Storage::disk('public')->path($image->thumbnail_file_path));
    expect($thumbnailDimensions[0])->toBe(400)->and($thumbnailDimensions[1])->toBe(400);
});

it('skips a row already carrying the _thumb.webp filename the pipeline produces, unless --force is passed', function () {
    $suggestion = Suggestion::factory()->create();
    putRawImage(400, 400, 'suggestions/images/abc123_thumb.webp');
    putRawImage(1000, 700, 'suggestions/images/abc123.webp');

    $image = $suggestion->images()->create([
        'file_path' => 'suggestions/images/abc123.webp',
        'thumbnail_file_path' => 'suggestions/images/abc123_thumb.webp',
    ]);

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    $image->refresh();
    expect($image->thumbnail_file_path)->toBe('suggestions/images/abc123_thumb.webp');

    $this->artisan('snappysnail:optimise-images --force')->assertExitCode(0);

    $image->refresh();
    expect($image->thumbnail_file_path)->not->toBe('suggestions/images/abc123_thumb.webp');
});

it('reprocesses a legacy image even if its old-named thumbnail happens to already be 400x400', function () {
    // Regression test: some production images were resized to a real 400x400
    // in place by a long-removed one-off script, but kept the *old* `_200_200`
    // filename. A dimensions-based "already done" check would wrongly skip
    // these forever — only the `_thumb.webp` filename marks true completion.
    $suggestion = Suggestion::factory()->create();
    putRawImage(1200, 800, 'suggestions/images/legacy.webp');
    putRawImage(400, 400, 'suggestions/images/legacy_200_200.webp');

    $image = $suggestion->images()->create([
        'file_path' => 'suggestions/images/legacy.webp',
        'thumbnail_file_path' => 'suggestions/images/legacy_200_200.webp',
    ]);

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    $image->refresh();
    expect($image->thumbnail_file_path)->not->toBe('suggestions/images/legacy_200_200.webp')
        ->and($image->thumbnail_file_path)->toEndWith('_thumb.webp');
});

it('reprocesses a file shared by a translation and its source together, without breaking the second row', function () {
    // Regression test: Suggestion::copyImagesFrom() gives a translation its own
    // Image row pointing at the *same* file_path as the source's row. Processing
    // rows one at a time deleted the shared file after the first row, leaving
    // the second row (e.g. the Italian translation) pointing at nothing.
    $source = Suggestion::factory()->create(['locale' => 'en']);
    $translation = Suggestion::factory()->create(['locale' => 'it']);

    putRawImage(1200, 800, 'suggestions/images/shared.jpg');

    $sourceImage = $source->images()->create([
        'file_path' => 'suggestions/images/shared.jpg',
        'thumbnail_file_path' => 'suggestions/images/shared.jpg',
    ]);
    $translationImage = $translation->images()->create([
        'file_path' => 'suggestions/images/shared.jpg',
        'thumbnail_file_path' => 'suggestions/images/shared.jpg',
    ]);

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    $sourceImage->refresh();
    $translationImage->refresh();

    expect($sourceImage->file_path)->not->toBe('suggestions/images/shared.jpg')
        ->and($translationImage->file_path)->not->toBe('suggestions/images/shared.jpg')
        ->and($translationImage->file_path)->toBe($sourceImage->file_path)
        ->and($translationImage->thumbnail_file_path)->toBe($sourceImage->thumbnail_file_path)
        ->and(Storage::disk('public')->exists($sourceImage->file_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($sourceImage->thumbnail_file_path))->toBeTrue();
});

it('reports failure and continues when the source file is missing', function () {
    $suggestion = Suggestion::factory()->create();

    $suggestion->images()->create([
        'file_path' => 'suggestions/images/missing.jpg',
        'thumbnail_file_path' => 'suggestions/images/missing.jpg',
    ]);

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    expect(Image::count())->toBe(1);
});
