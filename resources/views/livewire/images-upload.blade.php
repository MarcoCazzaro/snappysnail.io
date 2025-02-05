<div
    x-data="{ isUploading: false, progress: 1 }"
    x-on:livewire-upload-start="isUploading = true"
    x-on:livewire-upload-finish="isUploading = false; progress = 1; $wire.tempFilesUploadComplete()"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = ($event.detail.progress > progress) ? $event.detail.progress : progress">
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 justify-center">
        @if ($modelImages->count() > 0)
        @foreach($modelImages as $image)
        @if(gettype($image) !== "string")
        <x-images-upload-image :$image></x-images-upload-image>
        @endif
        @endforeach
        @endif
        @if (!empty($tempImages))
        @foreach($tempImages as $image)
        @if($image && !$errors->has('tempImages.*'))
        <x-images-upload-image :$image></x-images-upload-image>
        @endif
        @endforeach
        @endif
        @if(is_array($tempImages ?? false))
        @for($i = 1; $i < $minImagesCount - count($tempImages) - $modelImages->count() + 1; $i++)
            <label for="{{ $input_id }}" class="cursor-pointer rounded-lg text-zinc-300 border border-zinc-300 transition hover:border-brand hover:text-brand flex justify-center items-center aspect-square"><i class="fas fa-plus fa-6x"></i></label>
            @endfor
            @endif
            <div class="relative flex items-center justify-center items-center aspect-square max-w-[200px]">
                <label for="{{ $input_id }}" class="cursor-pointer text-zinc-300 border border-zinc-300 transition hover:border-brand hover:text-brand flex justify-center items-center w-20 h-20 rounded-full text-center"><i class="fas fa-plus fa-3x text-center"></i></label>
            </div>
            <input id="{{ $input_id }}" type="file" wire:model.live="tempImages" multiple accept=".png,.jpg,.jpeg" class="-z-10 absolute opacity-0">
            <input type="hidden" name="modelImagesIds" value="{{ $this->modelImagesIds }}">
            <input type="hidden" name="tempImagesPaths" value="{{ $this->tempImagesPaths }}">
            <input type="number" wire:model.live="validationTrigger" {{ ($minImagesCount > 0) ? 'required' : '' }} class="opacity-0 h-[1px]">
    </div>
    <x-input-error for="validationTrigger" class="mt-2" />
    <x-input-error for="modelImagesIds" class="mt-2" />

    <div class="my-5">
        <div x-show="isUploading" x-transition.duration.500ms class="hidden relative w-full h-1.5 rounded-full overflow-hidden" :class="{ 'hidden': ! isUploading }">
            <progress max="100" x-bind:value="progress" class="absolute w-full h-1.5
            [&::-webkit-progress-bar]:rounded-lg [&::-webkit-progress-value]:rounded-lg   [&::-webkit-progress-bar]:bg-gray-300 [&::-webkit-progress-value]:bg-brand [&::-moz-progress-bar]:bg-brand"></progress>
        </div>
    </div>
</div>