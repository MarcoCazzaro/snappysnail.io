<?php

namespace App\Livewire;

use App\Models\Image;
use App\Rules\AtLeastFiveImages;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImagesUpload extends Component
{
    use WithFileUploads;

    public $modelImages = null;

    public $tempImages = [];

    private $transitionalTempImages = [];

    public $input_id;

    public $minImagesCount = 1;

    public $validationTrigger = null;

    public $firstRun = true;

    #[Computed]
    public function modelImagesIds()
    {
        if (! $this->modelImages) {
            $this->modelImages = collect(new Image);
        }

        return implode(',', $this->modelImages->pluck('id')->toArray());
    }

    #[Computed]
    public function tempImagesPaths()
    {
        return implode(',', array_map(fn ($item) => $item = $item->getRealPath(), $this->tempImages));
    }

    public function rules()
    {
        return [
            'tempImages.*' => 'sometimes|image|mimes:png,jpg,jpeg|max:12288', // 12288 = 12MB Max
            'tempImagesPaths' => [new AtLeastFiveImages],
            'validationTrigger' => 'min:1',
        ];
    }

    public function mount(Collection $images = null, int $minImagesCount = 5)
    {
        $this->modelImages = $images;
        $this->minImagesCount = $minImagesCount;
        $this->input_id = uniqid('ssnail-images-uploader-');
        if (old('modelImagesIds')) {
            $old_ids = explode(',', old('modelImagesIds'));
            if (count($old_ids) > 0) {
                $this->modelImages = $this->modelImages->filter(function ($image, int $key) use ($old_ids) {
                    return in_array($image->id, $old_ids);
                });
            }
        }

        $this->checkValidation();
    }

    public function updatingTempImages($value)
    {
        $this->transitionalTempImages = array_merge($this->tempImages, $value);
    }

    public function updatedTempImages()
    {
        $this->tempImages = $this->transitionalTempImages;
        unset($this->tempImagesPaths);
        $this->checkValidation();
    }

    public function render()
    {
        return view('livewire.images-upload');
    }

    public function tempFilesUploadComplete()
    {
        //$this->emitUp('imagesUploaded', $this->modelImages, $this->mapTempFilePaths());
    }

    public function removeImage($id)
    {
        $this->modelImages = $this->modelImages->filter(function ($item, $key) use ($id) {
            return (int) $item['id'] !== (int) $id && strlen($item['file_path']) > 0;
        });
        unset($this->modelImagesIds);
        $this->checkValidation();
    }

    public function removeTempImage($file_path)
    {
        $this->tempImages = array_filter($this->tempImages, fn ($item) => $item->getFilename() !== $file_path);
        unset($this->tempImagesPaths);
        $this->checkValidation();
        //$this->emitUp('imagesUploaded', $this->modelImages, $this->mapTempFilePaths());
    }

    private function mapTempFilePaths()
    {
        unset($this->tempImagesPaths);
    }

    private function checkValidation()
    {
        $validator = Validator::make(
            [
                'tempImagesPaths' => $this->tempImagesPaths,
                'modelImagesIds' => $this->modelImagesIds,
            ],
            [
                'tempImagesPaths' => ['required_without:modelImagesIds', new AtLeastFiveImages],
                'modelImagesIds' => ['required_without:tempImagesPaths', new AtLeastFiveImages],
            ]
        );
        if ($validator->fails()) {
            $this->validationTrigger = null;
            if ($this->firstRun === false) {
                $this->addError('validationTrigger', 'Inserisci almeno 5 immagini');
            }
        } else {
            $this->validationTrigger = 1;
            $this->resetErrorBag('validationTrigger');
        }
        $this->firstRun = false;
    }
}
