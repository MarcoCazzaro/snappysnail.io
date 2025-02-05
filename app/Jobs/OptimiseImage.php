<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class OptimiseImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $image;

    /**
     * Create a new job instance.
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $path_base = storage_path('app/public/');
            $image_handler = InterventionImage::read($path_base . $this->image->file_path);
            $image_handler->scale(1920, 1080);
            $image_handler->toWebp(90)->save($path_base . $this->image->file_path);

            $image_handler = InterventionImage::read($path_base . $this->image->file_path);
            $image_handler->scaleDown(width: 400)->scaleDown(height: 400)->resizeCanvas(400, 400, 'transparent');
            $image_handler->toWebp(90)->save($path_base . $this->image->thumbnail_file_path, 90, 'webp');
            $this->image->optimised_at = now();
            $this->image->saveQuietly();
        } catch (\Exception $e) {
            report($e);
        }
    }
}
