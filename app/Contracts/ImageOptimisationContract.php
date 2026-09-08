<?php

namespace App\Contracts;

interface ImageOptimisationContract
{
    /**
     * Resize and re-encode the image at $sourcePath into a full-size WebP
     * (capped, never upscaled) and a cropped 400x400 WebP thumbnail, writing
     * both to the public disk.
     *
     * @return array{full: string, thumbnail: string} storage-relative paths
     */
    public function generate(string $sourcePath): array;
}
