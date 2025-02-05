<?php

namespace App\Models;

use App\Events\ImageDeleting;
use App\Events\ImageSaved;
use App\Services\ImageOptimisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'caption',
        'file_path',
        'thumbnail_file_path',
        'optimised_at',
    ];

    protected $dispatchesEvents = [
        'saved' => ImageSaved::class,
        'deleting' => ImageDeleting::class,
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getURLAttribute()
    {
        if ($this->optimised_at) {
            return ImageOptimisation::getPublicUrl($this->file_path) . '?optimised';
        } else {
            return ImageOptimisation::getPublicUrl($this->file_path);
        }
    }

    public function getThumbnailURLAttribute()
    {
        if ($this->optimised_at) {
            return ImageOptimisation::getPublicUrl($this->thumbnail_file_path) . '?optimised';
        } else {
            return ImageOptimisation::getPublicUrl($this->thumbnail_file_path);
        }
    }
}
