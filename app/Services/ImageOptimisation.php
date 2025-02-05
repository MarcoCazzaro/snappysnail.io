<?php

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ImageOptimisation
{
    public function generate($source_image): array
    {
        $image_paths = [
            'full' => null,
            'thumbnail' => null,
        ];
        try {
            $image_file = new File($source_image);
            $full_image_name = str_replace($image_file->extension(), 'webp', time() . $image_file->hashName());
            $full_path = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $full_image_name, 'public');
            $thumbnail_image_name = str_replace('.webp', '_200_200.webp', $full_image_name);
            $thumbnail_path = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $thumbnail_image_name, 'public');
            $image_paths = [
                'full' => $full_path,
                'thumbnail' => $thumbnail_path,
            ];
        } catch (\Exception $e) {
            report($e);
        }

        return $image_paths;
    }

    public static function getPublicUrl($partial_file_path)
    {
        return asset(Storage::url($partial_file_path));
    }
}
