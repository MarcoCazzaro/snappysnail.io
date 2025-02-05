<?php

namespace App\Events;

use App\Models\Image;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageDeleting
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $image;

    /**
     * Create a new event instance.
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }
}
