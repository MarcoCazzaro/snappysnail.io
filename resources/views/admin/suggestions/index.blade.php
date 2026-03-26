<x-app-layout>
    <x-slot name="header">
        <div class="grid gap-2">
            <h2 class="font-semibold text-xl text-zinc-800 leading-tight">
                {{ __('Suggestions') }}
            </h2>

            <div class="flex gap-8 flex-wrap">
                <a href="{{ route('suggestions.create') }}" class="flex gap-2 items-center ssnail-link">
                    <i class="fas fa-plus"></i>{{ __('Create') }}
                </a>

                <a href="{{ route('suggestions.export-untranslated') }}" class="flex gap-2 items-center ssnail-link">
                    <i class="fas fa-file-export"></i>{{ __('Generate translation JSON') }}
                </a>

                <div x-data>
                    <button type="button" @click="$refs.importFile.click()" class="flex gap-2 items-center ssnail-link font-bold">
                        <i class="fas fa-file-import"></i>{{ __('Upload translated JSON') }}
                    </button>
                    <form method="POST" action="{{ route('suggestions.import-translations') }}" enctype="multipart/form-data" x-ref="importForm">
                        @csrf
                        <input type="file" x-ref="importFile" name="file" accept=".json" class="hidden"
                               @change="$refs.importForm.submit()">
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        @if (session('status'))
            <div class="mx-auto sm:px-6 lg:px-8 mb-4">
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded-sm">{{ session('status') }}</div>
            </div>
        @endif

        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg">
                <div class="bg-white overflow-x-auto">
                    <table class="table-fixed w-full min-w-[1024px] wrap-break-word">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Title') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Keywords') }}
                                </th>
                                <th class="w-72 px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Locale') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('Sorting') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs leading-4 font-medium text-zinc-500 uppercase tracking-wider">
                                    {{ __('URL') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suggestions as $suggestion)
                                {{-- Parent / original row --}}
                                <tr class="border-t-2 border-zinc-300 bg-white">
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <div class="flex gap-2">
                                            <a href="{{ route('suggestions.edit', $suggestion) }}" class="flex gap-2 text-zinc-500 hover:text-zinc-700 transition-colors" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('suggestions.show', $suggestion) }}" class="flex gap-2 text-zinc-500 hover:text-zinc-700 transition-colors" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('suggestions.translate', $suggestion) }}">
                                                @csrf
                                                <button type="submit" class="flex gap-2 text-zinc-500 hover:text-brand transition-colors" title="{{ __('Translate') }}">
                                                    <i class="fas fa-language"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('suggestions.destroy', $suggestion) }}"
                                                  x-data
                                                  @submit.prevent="if (confirm('{{ __('Delete this suggestion?') }}')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex gap-2 text-zinc-500 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap font-semibold text-zinc-800">
                                        {{ $suggestion->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        {{ $suggestion->keywords }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        {{ \Str::limit(strip_tags($suggestion->description), 250) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold uppercase bg-zinc-800 text-white">
                                            {{ $suggestion->locale }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        {{ $suggestion->sorting }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap text-xs">
                                        <a href="{{ $suggestion->url }}" target="_blank" class="text-zinc-500 hover:text-zinc-700 transition-colors">
                                            {{ $suggestion->url }}
                                        </a>
                                    </td>
                                </tr>

                                {{-- Translation rows --}}
                                @foreach ($suggestion->translations as $translation)
                                    <tr class="border-t border-brand/30 bg-brand/5">
                                        <td class="pl-10 pr-6 py-3 whitespace-no-wrap">
                                            <div class="flex gap-2">
                                                <a href="{{ route('suggestions.edit', $translation) }}" class="flex gap-2 text-zinc-500 hover:text-zinc-700 transition-colors" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('suggestions.show', $translation) }}" class="flex gap-2 text-zinc-500 hover:text-zinc-700 transition-colors" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" action="{{ route('suggestions.destroy', $translation) }}"
                                                      x-data
                                                      @submit.prevent="if (confirm('{{ __('Delete this suggestion?') }}')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex gap-2 text-zinc-500 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap">
                                            <div class="flex items-center gap-2 pl-4 border-l-2 border-brand">
                                                <span class="text-brand text-sm select-none">↳</span>
                                                <span class="text-zinc-700">{{ $translation->title }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap text-zinc-600 text-sm">
                                            {{ $translation->keywords }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap text-zinc-600 text-sm">
                                            {{ \Str::limit(strip_tags($translation->description), 250) }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-bold uppercase bg-brand text-zinc-900">
                                                {{ $translation->locale }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap text-zinc-600 text-sm">
                                            {{ $translation->sorting }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-no-wrap text-xs">
                                            <a href="{{ $translation->url }}" target="_blank" class="text-zinc-500 hover:text-zinc-700 transition-colors">
                                                {{ $translation->url }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
