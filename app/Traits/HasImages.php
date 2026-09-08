<?php

namespace App\Traits;

use App\Contracts\ImageOptimisationContract;
use App\Jobs\DeletePhisicalImages;
use App\Models\Image;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasImages
{
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function latestImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    }

    public function oldestImage(): MorphOne
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
                $images_to_delete = array_merge(
                    $images_to_delete,
                    $this->images()->pluck('file_path')->toArray(),
                    $this->images()->pluck('thumbnail_file_path')->toArray(),
                );
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
                        if ($file_paths === false) {
                            continue;
                        }
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

    /**
     * Defensive: a file_path/thumbnail_file_path should no longer be shared by
     * more than one Image row (Suggestion::images() now delegates a
     * translation to its source instead of copying rows), but only queue a
     * file for deletion once no remaining Image row references it, as a
     * guard against any leftover legacy data that still does.
     */
    private static function deletePhisicalFiles(array $files)
    {
        $files = array_values(array_unique(array_filter($files)));

        if (empty($files)) {
            return;
        }

        $stillReferenced = Image::query()
            ->where(fn ($query) => $query->whereIn('file_path', $files)->orWhereIn('thumbnail_file_path', $files))
            ->get()
            ->flatMap(fn (Image $image) => [$image->file_path, $image->thumbnail_file_path])
            ->filter()
            ->all();

        $files = array_values(array_diff($files, $stillReferenced));

        if (empty($files)) {
            return;
        }

        $dispatch_method = (app()->environment('local') ? 'dispatchSync' : 'dispatch');
        DeletePhisicalImages::$dispatch_method($files);
    }

    private static function saveImageAndOptimisations(string $temp_image_path): array|false
    {
        try {
            return app(ImageOptimisationContract::class)->generate($temp_image_path);
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }
}
