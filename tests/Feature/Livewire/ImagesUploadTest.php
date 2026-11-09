<?php

use App\Livewire\ImagesUpload;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders an unpreviewable upload without throwing', function () {
    Storage::fake('local');

    Livewire::test(ImagesUpload::class, ['images' => new Collection])
        ->set('tempImages', [UploadedFile::fake()->create('favicon.ico', 10, 'image/x-icon')])
        ->assertOk()
        ->assertSee('favicon.ico');
});

it('renders a previewable upload as an image', function () {
    Storage::fake('local');

    Livewire::test(ImagesUpload::class, ['images' => new Collection])
        ->set('tempImages', [UploadedFile::fake()->image('photo.jpg')])
        ->assertOk()
        ->assertSeeHtml('<img src=');
});
