<?php
$jsonData = json_encode([
    'images' => [
        asset('img/services/3db8ccb6-6c0b-4493-b596-2f559d2261d6.jpeg'),
        asset('img/services/385a1b26-b869-4708-8fec-06461dcc9845.jpeg'),
        asset('img/services/665c697f-0fab-4c35-ab82-90a6dceb58ec.jpeg'),
        asset('img/services/8502af58-c0fc-4f33-89a7-ebf1e75cfe91.jpeg'),
        asset('img/services/9290c282-7528-454d-bfb6-edd41772e847.jpeg'),
        asset('img/services/9672f540-f6b4-4d4e-aa73-06f0194d6917.jpeg'),
        asset('img/services/9709c378-71fa-4f92-856c-f02f85240f96.jpeg'),
        asset('img/services/7757276b-daa3-4d5e-8334-52afecb58dce.jpeg'),
        asset('img/services/a07a4cd0-114d-421f-a413-025685485cff.jpeg'),
        asset('img/services/b30315d9-54bb-42e7-b2da-610113d14bfd.jpeg'),
        asset('img/services/f640f71f-669e-4b2e-b2d3-17f3ec1e187d.jpeg'),
        asset('img/services/f55972a6-a8ce-4aa8-9743-6e213056f7d4.jpeg'),
        asset('img/services/faf73648-16a9-4e85-9bec-ccff55a924a6.jpeg'),
    ],
    'labels' => [
        __('pages.services_label_web_dev'),
        __('pages.services_label_landing'),
        __('pages.services_label_outsourcing'),
        __('pages.services_label_consulting'),
    ]
]);
?>
<div
    x-data="{
        jsonData: JSON.parse('{{ $jsonData }}'),
        tilesIndexes: [],
        theChosenOne: 0,
        shuffleTiles() {
            this.tilesIndexes = Array.from({length: this.jsonData.images.length + this.jsonData.labels.length}, (_, i) => i + 1);
            this.tilesIndexes.sort(() => Math.random() - 0.5);
            this.theChosenOne = this.tilesIndexes[this.tilesIndexes.length - 1];
        }
    }" x-init="shuffleTiles">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-16" @scroll.window.throttle="shuffleTiles">
        <div class="md:col-span-4">
            <h2 class="mb-8">{{ __('pages.services_what_i_do_heading') }}</h2>
            <p>{{ __('pages.services_what_i_do_text') }}</p>
        </div>
        <div class="md:col-span-4">
            <h2 class="mb-8">{{ __('pages.services_heading') }}</h2>
            <div class="grid grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
                <template x-for="(image, index) in jsonData.images" :key="index">
                    <div class="bg-zinc-100 dark:bg-zinc-800 w-full h-auto aspect-[1/1] transition-[order] rounded-lg overflow-clip" :style="{order:(tilesIndexes[index] - 1)}" :class="{ 'col-span-2': index === theChosenOne }">
                        <img :src="image" alt="Services" class="object-cover w-full h-full">
                    </div>
                </template>
                <template x-for="(label, index) in jsonData.labels" :key="index">
                    <div class="bg-zinc-100 dark:bg-zinc-800 p-2 lg:p-4text-center w-full h-auto aspect-[1/1] rounded-lg overflow-clip grid place-items-center" :style="{order:(tilesIndexes[index + jsonData.images.length] - 1)}" :class="{ 'col-span-2': index + jsonData.images.length === theChosenOne }">
                        <label x-text="label" class="break-words text-xs lg:text-lg"></label>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
