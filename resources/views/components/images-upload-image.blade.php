<figure class="relative bg-gray-300 aspect-square max-w-[200px] rounded-lg overflow-hidden">
    <?php
    $is_temp_image = (method_exists($image, 'temporaryUrl'));
    $image_url = ($is_temp_image ? $image->temporaryUrl() : $image->thumbnail_url)
    ?>
    <img src="{{ $image_url }}" class="object-cover w-full h-full">
    <button type="button" class="absolute right-0 top-0 m-2 text-white bg-red-700 hover:bg-red-900 rounded-full p-1 z-10 w-6 h-6 flex justify-center items-center"
        @if($is_temp_image)
        wire:click="removeTempImage('{{ $image->getFilename() }}')"
        @else
        wire:click="removeImage('{{ $image->id }}')"
        @endif><i class="fas fa-times"></i></span></button>
</figure>