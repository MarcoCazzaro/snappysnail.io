<?php

namespace App\Traits;

use App\Jobs\DeletePhisicalImages;
use App\Models\Image;
use App\Services\ImageOptimisation;

trait HasImages
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function latestImage()
    {
        return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    }

    public function oldestImage()
    {
        return $this->morphOne(Image::class, 'imageable')->oldestOfMany();
    }

    public function syncImages($request)
    {
        try {
            $model_images_ids = explode(',', $request->modelImagesIds ?? '');
            $model_images = Image::whereIn('id', $model_images_ids)->get();
            $images_to_delete = [];
            if ($model_images && count($model_images) > 0) {
                foreach ($this->images as $image) {
                    if (! $model_images->contains('id', $image->id)) {
                        $images_to_delete = array_merge($images_to_delete, [
                            $image->file_path,
                            $image->thumbnail_file_path,
                        ]);
                        $this->images()->where('id', $image->id)->delete();
                    }
                }
            } else {
                $images_to_delete = array_merge($images_to_delete, [
                    $this->images()->pluck('file_path')->toArray(),
                    $this->images()->pluck('thumbnail_file_path')->toArray(),
                ]);
                $this->images()->delete();
            }
            if (count($images_to_delete) > 0) {
                self::deletePhisicalFiles($images_to_delete);
            }
        } catch (\Exception $e) {
            report($e);
        }
        try {
            $temp_images_paths = explode(',', $request->tempImagesPaths ?? '');
            if ($temp_images_paths && count($temp_images_paths) > 0) {
                $new_images = collect([]);
                foreach ($temp_images_paths as $temp_image_path) {
                    if (trim($temp_image_path) !== '') {
                        $file_paths = self::saveImageAndOptimisations($temp_image_path);
                        $new_images->add([
                            'file_path' => $file_paths['full'],
                            'thumbnail_file_path' => $file_paths['thumbnail'],
                        ]);
                    }
                }
                $this->images()->createMany($new_images->toArray());
            }
        } catch (\Exception $e) {
            report($e);
        }
        $this->load('images');

        return true;
    }

    private static function deletePhisicalFiles(array $files)
    {
        $dispatch_method = (app()->environment('local') ? 'dispatchSync' : 'dispatch');
        DeletePhisicalImages::$dispatch_method($files);
    }

    private static function saveImageAndOptimisations($temp_image_path)
    {
        try {
            $image_handler = new ImageOptimisation();
            $file_paths = $image_handler->generate($temp_image_path);
            $image_handler = null;
        } catch (\Exception $e) {
            report($e);
            $file_paths = false;
        }

        return $file_paths;
    }
}
