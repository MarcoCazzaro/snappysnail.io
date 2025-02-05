<?php

namespace Database\Factories;

use App\Services\ImageOptimisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fake_image_path = 'database/data/faker_images/stock-image-house-'.rand(1, 5).'.jpg';
        $image_handler = new ImageOptimisation();
        $file_paths = $image_handler->generate($fake_image_path);
        $image_handler = null;

        return [
            'caption' => fake()->sentence(),
            'file_path' => $file_paths['full'],
            'thumbnail_file_path' => $file_paths['thumbnail'],
        ];
    }
}
