<x-app-layout>
    <x-slot name="header">
        <div class="grid gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $suggestion->title }}
            </h2>

            <div class="flex gap-4">
                <a href="{{ route('suggestions.edit', $suggestion) }}" class="flex gap-2 items-center ssnail-link">
                    <i class="fas fa-edit"></i>{{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('suggestions.translate', $suggestion) }}">
                    @csrf
                    <button type="submit" class="flex gap-2 items-center ssnail-link">
                        <i class="fas fa-language"></i>{{ __('Translate') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('components.suggestion-item', $suggestion)
                </div>
            </div>
        </div>
    </div>
</x-app-layout>