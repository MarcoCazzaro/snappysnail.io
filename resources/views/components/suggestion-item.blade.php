<div class="suggestion-item group {{ str_replace(',', ' ', $suggestion->keywords ?? '') }}">
    @if($suggestion->images->count() > 0)
    @php $hasMultipleImages = $suggestion->images->count() > 1; @endphp
    <div x-data="{ currentIndex: 0, lightboxOpen: false }" class="gallery mb-8 relative h-auto w-full aspect-square bg-zinc-200 dark:bg-zinc-900 rounded-lg">
        @foreach($suggestion->images as $index => $image)
        <img loading="lazy" src="{{ $image->thumbnail_url }}" alt="Suggestion image" class="absolute rounded-lg opacity-0 h-full w-full object-cover transition-all contrast-75 group-hover:contrast-100 {{ $hasMultipleImages ? 'cursor-zoom-in' : '' }}" x-show="{{ $index }} === currentIndex" x-transition.opacity.duration.500ms :class="{ 'opacity-100': currentIndex === {{ $index }} }" @if($hasMultipleImages) @click="lightboxOpen = true" @endif>
        @endforeach
        <button aria-label="Previous image" @click="currentIndex = (currentIndex - 1 + {{ count($suggestion->images) }}) % {{ count($suggestion->images) }}" class="absolute top-1/2 left-0 transform -translate-y-1/2 -translate-x-3/4 w-12 h-12 text-black dark:text-white hover:text-brand dark:hover:text-brand transition-all opacity-25 group-hover:opacity-100">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button aria-label="Next image" @click="currentIndex = (currentIndex + 1) % {{ count($suggestion->images) }}" class="absolute top-1/2 right-0 transform -translate-y-1/2 translate-x-3/4 w-12 h-12 text-black dark:text-white hover:text-brand dark:hover:text-brand transition-all opacity-25 group-hover:opacity-100">
            <i class="fas fa-chevron-right"></i>
        </button>

        @if($hasMultipleImages)
        <template x-teleport="body">
            <div x-show="lightboxOpen" x-cloak x-transition.opacity
                 x-on:keydown.escape.window="lightboxOpen = false"
                 x-on:keydown.arrow-left.window="currentIndex = (currentIndex - 1 + {{ count($suggestion->images) }}) % {{ count($suggestion->images) }}"
                 x-on:keydown.arrow-right.window="currentIndex = (currentIndex + 1) % {{ count($suggestion->images) }}"
                 x-effect="document.body.classList.toggle('overflow-hidden', lightboxOpen)"
                 @click.self="lightboxOpen = false"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 sm:p-8">
                <button aria-label="Close" @click="lightboxOpen = false" class="absolute top-4 right-4 w-10 h-10 text-white hover:text-brand transition-colors">
                    <i class="fas fa-times fa-xl"></i>
                </button>
                <button aria-label="Previous image" @click="currentIndex = (currentIndex - 1 + {{ count($suggestion->images) }}) % {{ count($suggestion->images) }}" class="absolute top-1/2 left-2 sm:left-4 -translate-y-1/2 w-12 h-12 text-white hover:text-brand transition-colors">
                    <i class="fas fa-chevron-left fa-xl"></i>
                </button>
                @foreach($suggestion->images as $index => $image)
                <img loading="lazy" src="{{ $image->url }}" alt="Suggestion image" class="max-h-full max-w-full object-contain" x-show="{{ $index }} === currentIndex">
                @endforeach
                <button aria-label="Next image" @click="currentIndex = (currentIndex + 1) % {{ count($suggestion->images) }}" class="absolute top-1/2 right-2 sm:right-4 -translate-y-1/2 w-12 h-12 text-white hover:text-brand transition-colors">
                    <i class="fas fa-chevron-right fa-xl"></i>
                </button>
            </div>
        </template>
        @endif
    </div>
    @elseif(\Str::startsWith($suggestion->keywords ?? '', 'work') ?? false)
    <div class="gallery mb-8 relative h-auto w-full aspect-square">
        <div class="absolute inset-0 bg-zinc-200 dark:bg-zinc-900 rounded-lg"></div>
    </div>
    @endif
    @if(in_array(strtolower($suggestion->title), ['curriculum', 'services', 'contact']))
    <h1 class="title mb-16 font-semibold">{{ $suggestion->title }}</h1>
    @else
    @if($suggestion->sorting > 200000)
    <label class="text-sm font-semibold text-zinc-500">{{ \Str::substr($suggestion->sorting, 0, 4) }}</label>
    @elseif($suggestion->sorting == 200000)
    <label class="text-sm font-semibold text-zinc-500">{{ now()->format('Y') }}</label>
    @endif
    <h3 class="title mb-4 text-xl font-semibold">
        @if($suggestion->url && $suggestion->url !== '')
        <a href="{{ $suggestion->url }}" target="_blank" class="ssnail-link">
            <div class="flex items-top gap-2 justify-between">
                <span>{{ $suggestion->title }}</span><i class="fas fa-external-link-alt"></i>
            </div>
        </a>
        @else
        {{ $suggestion->title }}
        @endif
    </h3>
    @endif
    <div class="description mb-4">
        {!! $suggestion->description !!}
    </div>
    <div class="keywords text-xs mb-4 flex flex-wrap gap-2">
        @foreach(explode(',', $suggestion->keywords) as $keyword)
        <span class="keyword mr-2 text-gray-600 dark:text-gray-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-lg">{{ $keyword }}</span>
        @endforeach
    </div>
</div>