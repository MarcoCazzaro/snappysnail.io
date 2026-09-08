<?php

namespace App\Console\Commands;

use App\Contracts\ImageOptimisationContract;
use App\Models\Image;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[Signature('snappysnail:optimise-images {--force : Reprocess every row, including ones that already look optimised}')]
#[Description('Reprocess existing Image rows through the current optimisation pipeline')]
class OptimiseExistingImages extends Command
{
    /**
     * Backfill for images uploaded before the optimisation pipeline covered the
     * controller path (admin-panel uploads used to be stored raw, at native
     * dimensions, with a byte-identical "thumbnail"). Reruns every Image row
     * through ImageOptimisationContract::generate(), replacing its file paths
     * and deleting the stale files. Safe to rerun: a row whose thumbnail is
     * already a 400x400 square is skipped unless --force is passed.
     */
    public function handle(ImageOptimisationContract $optimiser): int
    {
        $images = Image::query()->get();
        $force = (bool) $this->option('force');

        $reprocessed = 0;
        $skipped = 0;

        foreach ($images as $image) {
            try {
                if (! $force && $this->alreadyOptimised($image)) {
                    $skipped++;

                    continue;
                }

                $this->reprocess($image, $optimiser);
                $reprocessed++;
                $this->info("Reprocessed: image #{$image->id}");
            } catch (Throwable $exception) {
                $this->error("Failed to reprocess image #{$image->id}: {$exception->getMessage()}");
                report($exception);
            }
        }

        $this->info("Done. Reprocessed {$reprocessed}, skipped {$skipped} (already optimised).");

        return self::SUCCESS;
    }

    private function alreadyOptimised(Image $image): bool
    {
        $path = Storage::disk('public')->path($image->thumbnail_file_path);

        if (! is_file($path)) {
            return false;
        }

        $dimensions = getimagesize($path);

        return $dimensions !== false && $dimensions[0] === 400 && $dimensions[1] === 400;
    }

    private function reprocess(Image $image, ImageOptimisationContract $optimiser): void
    {
        $oldFullPath = $image->file_path;
        $oldThumbnailPath = $image->thumbnail_file_path;

        $sourcePath = Storage::disk('public')->path($oldFullPath);

        if (! is_file($sourcePath)) {
            throw new RuntimeException("Source file missing at {$sourcePath}");
        }

        $newPaths = $optimiser->generate($sourcePath);

        $image->file_path = $newPaths['full'];
        $image->thumbnail_file_path = $newPaths['thumbnail'];
        $image->save();

        Storage::disk('public')->delete(array_filter([$oldFullPath, $oldThumbnailPath]));
    }
}
