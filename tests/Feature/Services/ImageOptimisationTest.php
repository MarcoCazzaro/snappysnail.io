<?php

use App\Contracts\ImageOptimisationContract;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

beforeEach(function () {
    Storage::fake('public');
});

function makeSourceImage(int $width, int $height): string
{
    $path = tempnam(sys_get_temp_dir(), 'ssnail-source-').'.png';

    (new ImageManager(new Driver))->createImage($width, $height)->fill('ff0000')->save($path);

    return $path;
}

afterEach(function () {
    array_map('unlink', glob(sys_get_temp_dir().'/ssnail-source-*'));
});

it('writes a full and a thumbnail webp to the public disk', function () {
    $paths = app(ImageOptimisationContract::class)->generate(makeSourceImage(800, 600));

    expect($paths)->toHaveKeys(['full', 'thumbnail'])
        ->and(Storage::disk('public')->exists($paths['full']))->toBeTrue()
        ->and(Storage::disk('public')->exists($paths['thumbnail']))->toBeTrue()
        ->and($paths['full'])->toEndWith('.webp')
        ->and($paths['thumbnail'])->toEndWith('.webp');
});

it('never upscales an image smaller than the full-size cap', function () {
    $paths = app(ImageOptimisationContract::class)->generate(makeSourceImage(800, 600));

    $dimensions = getimagesize(Storage::disk('public')->path($paths['full']));

    expect($dimensions[0])->toBe(800)
        ->and($dimensions[1])->toBe(600);
});

it('scales down an image larger than the full-size cap, preserving aspect ratio', function () {
    $paths = app(ImageOptimisationContract::class)->generate(makeSourceImage(3840, 2160));

    $dimensions = getimagesize(Storage::disk('public')->path($paths['full']));

    expect($dimensions[0])->toBeLessThanOrEqual(1920)
        ->and($dimensions[1])->toBeLessThanOrEqual(1080)
        ->and(round($dimensions[0] / $dimensions[1], 2))->toBe(round(3840 / 2160, 2));
});

it('produces a true 400x400 cropped square thumbnail regardless of source aspect ratio', function () {
    $paths = app(ImageOptimisationContract::class)->generate(makeSourceImage(1200, 300));

    $dimensions = getimagesize(Storage::disk('public')->path($paths['thumbnail']));

    expect($dimensions[0])->toBe(400)
        ->and($dimensions[1])->toBe(400);
});
