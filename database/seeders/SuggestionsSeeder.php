<?php

namespace Database\Seeders;

use App\Jobs\OptimiseImage;
use App\Models\Suggestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class SuggestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get('database/seeders/data/suggestions.json');
        $suggestions = json_decode($json);

        foreach ($suggestions as $suggestion) {
            $data = (array) $suggestion;
            if (\Str::startsWith($data['description'] ?? '', 'views.')) {
                // Get the view content
                $data['description'] = (string) View::make(str_replace('views.', '', $data['description']));
            }
            $suggestion = Suggestion::firstOrCreate(['title' => $data['title']], $data);
            if (! $suggestion->wasRecentlyCreated) {
                continue;
            }
            $keywords = explode(',', $data['keywords'] ?? '');
            $request = ['tempImagesPaths' => []];
            if ($keywords && in_array('works', $keywords) && count($keywords) > 2) {
                $folder_name = $keywords[2];
                $path = database_path('seeders/data/images/works/'.$folder_name);
                if (File::exists($path)) {
                    $files = File::files($path);
                    foreach ($files as $file) {
                        if (in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $request['tempImagesPaths'][] = $file->getRealPath();
                        }
                    }
                }
            }
            if (count($request['tempImagesPaths']) > 0) {
                $request['tempImagesPaths'] = implode(',', $request['tempImagesPaths']);
                // Convert into an object
                $request = (object) $request;
                $suggestion->syncImages($request);
                foreach ($suggestion->images as $image) {
                    OptimiseImage::dispatchSync($image);
                }
            }
        }
    }
}
