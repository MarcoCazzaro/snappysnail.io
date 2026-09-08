<?php

namespace App\Models;

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
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getURLAttribute()
    {
        return ImageOptimisation::getPublicUrl($this->file_path);
    }

    public function getThumbnailURLAttribute()
    {
        return ImageOptimisation::getPublicUrl($this->thumbnail_file_path);
    }
}
