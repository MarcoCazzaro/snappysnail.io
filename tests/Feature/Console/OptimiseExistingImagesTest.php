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

it('skips an image already processed into a 400x400 thumbnail unless --force is passed', function () {
    $suggestion = Suggestion::factory()->create();
    putRawImage(400, 400, 'suggestions/images/already-thumb.webp');

    $image = $suggestion->images()->create([
        'file_path' => 'suggestions/images/full.webp',
        'thumbnail_file_path' => 'suggestions/images/already-thumb.webp',
    ]);
    putRawImage(1000, 700, 'suggestions/images/full.webp');

    $this->artisan('snappysnail:optimise-images')->assertExitCode(0);

    $image->refresh();
    expect($image->thumbnail_file_path)->toBe('suggestions/images/already-thumb.webp');

    $this->artisan('snappysnail:optimise-images --force')->assertExitCode(0);

    $image->refresh();
    expect($image->thumbnail_file_path)->not->toBe('suggestions/images/already-thumb.webp');
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
