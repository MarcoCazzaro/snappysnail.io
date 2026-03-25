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
     */
    public function compose(View $view): void
    {
        $request = request();
        try {
            if ($view->statusCode ?? false) {
                $SEOKey = 'error_'.$view->statusCode;
            } else {
                $SEOKey = optional($request->route())->getName() ?? $view->whatever ?? '';
                $SEOKey = str_ireplace('.', '_', $SEOKey);
                $SEOKey = preg_replace('/^locale_/', '', $SEOKey);
            }
            $current_language = app()->getLocale();
            \Carbon\Carbon::setLocale($current_language);
            $seo_args = [
                'SNAIL_SEO_LANGUAGE' => $current_language,
                'SNAIL_SEO_TITLE' => trans('seo.default_title'),
                'SNAIL_SEO_TITLE_FULL' => trans('seo.default_title'),
                'SNAIL_SEO_DESCRIPTION' => trans('seo.default_description'),
                'SNAIL_SEO_KEYWORDS' => trans('seo.default_keywords'),
            ];
            $seo_key = 'seo.'.$SEOKey;
            $args = ['title', 'description', 'keywords'];
            $request = request();
            $bindings = $this->retrieveBindings($request);
            $fallback_value = false;
            foreach ($args as $arg) {
                $seo_full_key = $seo_key.'_'.$arg;
                switch (true) {
                    case \Lang::has($seo_full_key):
                        $seo_args['SNAIL_SEO_'.strtoupper($arg)] = trans($seo_full_key, $bindings);
                        if ($arg === 'title') {
                            $fallback_value = $seo_args['SNAIL_SEO_TITLE'];
                        }
                        break;

                    default:
                        if ($fallback_value) {
                            $seo_args['SNAIL_SEO_'.strtoupper($arg)] = $fallback_value;
                        }
                        break;
                }
            }
            $seo_args['SNAIL_SEO_DESCRIPTION'] = \Str::limit($seo_args['SNAIL_SEO_DESCRIPTION'], 151);
            $seo_args['SNAIL_SEO_TITLE_FULL'] = $seo_args['SNAIL_SEO_TITLE'];
            if (! \Str::contains($seo_args['SNAIL_SEO_TITLE_FULL'], 'Snappysnail')) {
                $seo_args['SNAIL_SEO_TITLE_FULL'] = $seo_args['SNAIL_SEO_TITLE_FULL'].' | Snappysnail';
            }
        } catch (\Exception $e) {
            report($e);
            $seo_args = null;
        }
        $view->with($seo_args);
    }

    private function retrieveBindings($request): array
    {
        $bindings = [];
        try {
            if ($request->user() ?? false) {
                $bindings['user_full_name'] = $request->user()->name ?? '';
            }
            $ad = $request->match->selling_ad ?? $request->ad ?? false;
            if ($ad) {
                $bindings['ad_title'] = $ad->title ?? '-';
            }
            $taxonomy = $request->taxonomy ?? false;
            if ($taxonomy) {
                $bindings['taxonomy_name'] = $taxonomy->name ?? '-';
            }
            $user = $request->user ?? false;
            if ($user) {
                $bindings['user_name'] = $user->name ?? '-';
            }
            $faq = $request->faq ?? false;
            if ($faq) {
                $bindings['faq_question'] = $faq->question ?? '-';
            }
        } catch (\Exception $e) {
            report($e);
        }

        return $bindings;
    }
}
