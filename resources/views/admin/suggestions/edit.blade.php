<?php
$mode = ($suggestion ?? false) ? 'edit' : 'create';
$action = ($suggestion ?? false) ? 'update' : 'store';
$action_label = ($suggestion ?? false) ? 'Edit' : 'Create';
$images = $suggestion->images ?? null;
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($action_label . ' Suggestion') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('suggestions.' . $action, ($suggestion->id ?? null)) }}" class="space-y-6">
                        @csrf
                        @if($mode === 'edit')
                        @method('PUT')
                        @endif

                        <div class="flex flex-col space-y-2">
                            <label for="title" class="text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                            <input id="title" type="text" name="title" value="{{ $suggestion->title ?? '' }}" required autofocus class="border border-gray-300 rounded-lg p-2 w-full">
                            <x-input-error for="title" class="mt-2" />
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="keywords" class="text-sm font-medium text-gray-700">{{ __('Keywords') }}</label>
                            <input id="keywords" type="text" name="keywords" value="{{ $suggestion->keywords ?? '' }}" required class="border border-gray-300 rounded-lg p-2 w-full">
                            <x-input-error for="keywords" class="mt-2" />
                        </div>

                        <div class="flex flex-col">
                            <label for="description" class="text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                            <x-quill-editor
                                name="description"
                                :value="old('description', $suggestion->description ?? '')"
                                placeholder="Content here..." />
                            <x-input-error for="description" class="mt-2" />
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="locale" class="text-sm font-medium text-gray-700">{{ __('Locale') }}</label>
                            <input id="locale" type="text" name="locale" value="{{ $suggestion->locale ?? '' }}" required class="border border-gray-300 rounded-lg p-2 w-full">
                            <x-input-error for="locale" class="mt-2" />
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="sorting" class="text-sm font-medium text-gray-700">{{ __('Sorting') }}</label>
                            <input id="sorting" type="number" name="sorting" value="{{ $suggestion->sorting ?? null }}" required class="border border-gray-300 rounded-lg p-2 w-full">
                            <x-input-error for="sorting" class="mt-2" />
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="url" class="text-sm font-medium text-gray-700">{{ __('URL') }}</label>
                            <input id="url" type="text" name="url" value="{{ $suggestion->url ?? '' }}" class="border border-gray-300 rounded-lg p-2 w-full">
                            <x-input-error for="url" class="mt-2" />
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="images" class="text-sm font-medium text-gray-700">{{ __('Images') }}</label>
                            <livewire:images-upload :$images min-images-count="0" />
                        </div>

                        <button type="submit" class="bg-brand hover:bg-brand-hover transition-colors text-white font-bold py-2 px-4 rounded-lg">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('header-styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <!-- Initialize Quill editor -->
    <script>
        /*
        const quill = new Quill('#description', {
            theme: 'snow'
        });
        quill.setContents('Ciao');
        */
    </script>
    @endpush
</x-app-layout>