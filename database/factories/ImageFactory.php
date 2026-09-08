<?php

namespace Database\Factories;

use App\Contracts\ImageOptimisationContract;
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
        $file_paths = app(ImageOptimisationContract::class)->generate($fake_image_path);

        return [
            'caption' => fake()->sentence(),
            'file_path' => $file_paths['full'],
            'thumbnail_file_path' => $file_paths['thumbnail'],
        ];
    }
}
