<?php

namespace App\View\Composers;

use Illuminate\View\View;

class SEOComposer
{
    public function __construct()
    {
        // Dependencies automatically resolved by service container...
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $request = request();
        $URL_segments = $request->segments();
        if (request()->is('it') || request()->is('it/*')) {
            $current_language = 'it_IT';
        } else {
            $current_language = 'en_GB';
        }
        \Carbon\Carbon::setLocale($current_language);
        try {
            $seo_args = [
                'SNAIL_SEO_LANGUAGE' => $current_language,
                'SNAIL_SEO_TITLE' => trans('seo.default.title'),
                'SNAIL_SEO_DESCRIPTION' => trans('seo.default.description'),
                'SNAIL_SEO_KEYWORDS' => trans('seo.default.keywords'),
            ];

            $seo_key = 'seo.' . implode(".", explode("/", request()->path()));
            $args = ['title', 'description', 'keywords'];
            foreach ($args as $arg) {
                $seo_full_key = $seo_key . "." . $arg;
                switch (true) {
                    case \Lang::has($seo_full_key):
                        $seo_args["SNAIL_SEO_".strtoupper($arg)] = trans($seo_full_key);
                        break;
                    case isset($view->active_taxonomy):
                        /*
                        $taxonomy_keywords = $view->active_taxonomy->label . ', ' . ($view->root_taxonomy_name ?? '') . ' ' . trans('common.by') . ' Debora Antonello';
                        $taxonomy_description = $view->active_taxonomy->description ?? $taxonomy_keywords;
                        $seo_args["SNAIL_SEO_".strtoupper($arg)] = trans('seo.works_taxonomy_' . $arg, [
                                'active_taxonomy' => $view->active_taxonomy->label,
                                'taxonomy_description' => strip_tags($taxonomy_description),
                                'taxonomy_keywords' => $taxonomy_keywords,
                            ]);
                        */
                        break;

                    default:
                        //
                        break;
                }
            }
        } catch (\Exception $e) {
            report($e);
        }
        $seo_args['SNAIL_SEO_DESCRIPTION'] = \Str::limit($seo_args['SNAIL_SEO_DESCRIPTION'], 151);
        if (!\Str::contains($seo_args['SNAIL_SEO_TITLE'], 'Snappysnail')) {
            $seo_args['SNAIL_SEO_TITLE'] = $seo_args['SNAIL_SEO_TITLE'] . " | Snappysnail";
        }
        $view->with($seo_args);
    }
}
