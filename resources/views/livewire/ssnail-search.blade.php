<div x-data x-init="$refs.searchField.focus()">
    <div class="container mx-auto">
        <div class="md:max-w-6xl mx-auto my-9 px-6">
            <div class="ssnail-search">
                <label for="searchField" class="hidden">{{ __('pages.search_snail_speed') }}</label>
                <input aria-label="Search" type="search" class="mt-1 block w-full rounded-md bg-white border-transparent focus:border-gray-200 focus:bg-white focus:ring-0 shadow-lg" wire:model.live="searchTerms" id="searchField" x-ref="searchField" autocomplete="off" @keyup="animating = false">
            </div>
        </div>
        <div class="md:max-w-6xl mx-auto text-gray-400 text-center" x-show="animating">
            <div class="ssnail-explanation">
                <p class="my-9 px-6 ssnail-1">{{ __('pages.search_snail_speed') }}</p>
                <p class="my-9 px-6 ssnail-2">{{ __('pages.search_loading_faster') }}</p>
                <p class="my-9 px-6 ssnail-3">{{ __('pages.search_speed_relative') }}</p>
            </div>
        </div>
    </div>

    <div class="mx-auto my-16 px-6">
        @if($no_results ?? false)
        <p class="my-9 text-gray-500">{{ __('pages.search_no_results') }} <a href="{{ url(app()->getLocale() . '/portfolio') }}">portfolio</a> or <a href="{{ url(app()->getLocale() . '/curriculum') }}">curriculum</a> or maybe <a href="{{ url(app()->getLocale() . '/contact') }}">contact</a></p>
        @elseif($too_short ?? false)
        <p class="my-9 text-gray-500">{{ __('pages.search_too_short') }}</p>
        @else
        @if($suggestions)
        <?php
        $columns = count($suggestions);
        if (\Str::startsWith($suggestions->first()->keywords ?? '', 'work')) {
            $columns = 4;
        }
        switch ($columns) {
            case 1:
                $cols_class = '';
                break;
            case 2:
                $cols_class = 'md:grid-cols-2';
                break;
            case 3:
                $cols_class = 'md:grid-cols-2 lg:grid-cols-3';
                break;
            default:
                $cols_class = 'md:grid-cols-2 lg:grid-cols-4';
                break;
        }
        ?>
        <div class="grid gap-16 {{ $cols_class }}">
            @each('components.suggestion-item', $suggestions, 'suggestion')
        </div>
        @endif
        @endif
    </div>
</div>
