<?php

namespace App\Services;

use App\Contracts\ImageOptimisationContract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ImageOptimisation implements ImageOptimisationContract
{
    private const DIRECTORY = 'suggestions/images';

    private const MAX_WIDTH = 1920;

    private const MAX_HEIGHT = 1080;

    private const THUMBNAIL_SIZE = 400;

    public const QUALITY = 90;

    public function __construct(private readonly ImageManager $manager) {}

    public function generate(string $sourcePath): array
    {
        $name = time().Str::random(12);
        $fullPath = self::DIRECTORY.'/'.$name.'.webp';
        $thumbnailPath = self::DIRECTORY.'/'.$name.'_thumb.webp';

        $full = $this->manager->read($sourcePath)
            ->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT)
            ->toWebp(self::QUALITY);

        $thumbnail = $this->manager->read($sourcePath)
            ->cover(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE)
            ->toWebp(self::QUALITY);

        Storage::disk('public')->put($fullPath, (string) $full);
        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail);

        return [
            'full' => $fullPath,
            'thumbnail' => $thumbnailPath,
        ];
    }

    public static function getPublicUrl(string $partialFilePath): string
    {
        return asset(Storage::url($partialFilePath));
    }
}
