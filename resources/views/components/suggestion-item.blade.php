<div class="suggestion-item group {{ str_replace(',', ' ', $suggestion->keywords ?? '') }}">
    @if($suggestion->images->count() > 0)
    <div x-data="{ currentIndex: 0 }" class="gallery mb-8 relative h-auto w-full aspect-[1/1] bg-zinc-200 dark:bg-zinc-900 rounded-lg">
        @foreach($suggestion->images as $index => $image)
        <img src="{{ $image->thumbnail_url }}" alt="Suggestion image" class="absolute rounded-lg opacity-0 h-full w-full object-cover transition-all contrast-75 group-hover:contrast-100" x-show="{{ $index }} === currentIndex" x-transition.opacity.duration.500ms :class="{ 'opacity-100': currentIndex === {{ $index }} }">
        @endforeach
        <button aria-label="Previous image" @click="currentIndex = (currentIndex - 1 + {{ count($suggestion->images) }}) % {{ count($suggestion->images) }}" class="absolute top-1/2 left-0 transform -translate-y-1/2 -translate-x-3/4 w-12 h-12 text-black dark:text-white hover:text-brand hover:dark:text-brand transition-all opacity-25 group-hover:opacity-100">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button aria-label="Next image" @click="currentIndex = (currentIndex + 1) % {{ count($suggestion->images) }}" class="absolute top-1/2 right-0 transform -translate-y-1/2 translate-x-3/4 w-12 h-12 text-black dark:text-white hover:text-brand hover:dark:text-brand transition-all opacity-25 group-hover:opacity-100">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    @elseif(\Str::startsWith($suggestion->keywords ?? '', 'work') ?? false)
    <div class="gallery mb-8 relative h-auto w-full aspect-[1/1]">
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