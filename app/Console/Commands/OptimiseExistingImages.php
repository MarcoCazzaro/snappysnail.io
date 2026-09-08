<?php

namespace App\Console\Commands;

use App\Contracts\ImageOptimisationContract;
use App\Models\Image;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[Signature('snappysnail:optimise-images {--force : Reprocess every group, including ones that already look optimised}')]
#[Description('Reprocess existing Image rows through the current optimisation pipeline')]
class OptimiseExistingImages extends Command
{
    /**
     * Backfill for images uploaded before the optimisation pipeline covered the
     * controller path (admin-panel uploads used to be stored raw, at native
     * dimensions, with a byte-identical "thumbnail"). Reruns every distinct
     * physical file through ImageOptimisationContract::generate() once,
     * replacing the file paths on every Image row that shares it (a
     * translation's row points at the same file as its source's — see
     * Suggestion::copyImagesFrom()), then deletes the stale files. Safe to
     * rerun: a group already carrying the `_thumb.webp` filename this
     * pipeline produces is skipped unless --force is passed.
     */
    public function handle(ImageOptimisationContract $optimiser): int
    {
        $force = (bool) $this->option('force');
        $groups = Image::query()->get()->groupBy('file_path');

        $reprocessedFiles = 0;
        $reprocessedRows = 0;
        $skippedRows = 0;

        foreach ($groups as $filePath => $images) {
            try {
                if (! $force && $this->alreadyMigrated($images->first())) {
                    $skippedRows += $images->count();

                    continue;
                }

                $this->reprocessGroup($images, $optimiser);
                $reprocessedFiles++;
                $reprocessedRows += $images->count();
                $this->info("Reprocessed: {$filePath} ({$images->count()} row(s))");
            } catch (Throwable $exception) {
                $this->error("Failed to reprocess {$filePath}: {$exception->getMessage()}");
                report($exception);
            }
        }

        $this->info("Done. Reprocessed {$reprocessedFiles} file(s) across {$reprocessedRows} row(s), skipped {$skippedRows} row(s) (already optimised).");

        return self::SUCCESS;
    }

    /**
     * The only reliable signal that a row has already gone through the current
     * ImageOptimisation::generate() is its `_thumb.webp` filename suffix, which
     * only that method ever produces. Checking pixel dimensions instead is not
     * safe: some legacy images were resized to a real 400x400 in place by a
     * long-removed one-off script, under the *old* `_200_200` filename — that
     * coincidence made an earlier version of this check wrongly skip them.
     */
    private function alreadyMigrated(Image $image): bool
    {
        return str_ends_with($image->thumbnail_file_path ?? '', '_thumb.webp');
    }

    /**
     * @param  Collection<int, Image>  $images  every row sharing one physical file
     */
    private function reprocessGroup(Collection $images, ImageOptimisationContract $optimiser): void
    {
        $oldFullPath = $images->first()->file_path;
        $oldThumbnailPath = $images->first()->thumbnail_file_path;

        $sourcePath = Storage::disk('public')->path($oldFullPath);

        if (! is_file($sourcePath)) {
            throw new RuntimeException("Source file missing at {$sourcePath}");
        }

        $newPaths = $optimiser->generate($sourcePath);

        foreach ($images as $image) {
            $image->file_path = $newPaths['full'];
            $image->thumbnail_file_path = $newPaths['thumbnail'];
            $image->save();
        }

        Storage::disk('public')->delete(array_filter([$oldFullPath, $oldThumbnailPath]));
    }
}
