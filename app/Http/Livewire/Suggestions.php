<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Suggestion;

class Suggestions extends Component
{
	use WithPagination;

	public $suggestion_id, $name, $keywords, $description, $locale, $path, $view;
    public $isModalOpen = 0;

    public function render()
    {
        return view('livewire.suggestions', [
        	'suggestions' => Suggestion::orderBy('id', 'desc')->paginate(20)
        ]);
    }

    public function create()
    {
        $this->resetCreateForm();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
        $this->resetErrorBag();
		$this->resetValidation();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetCreateForm()
    {
        $this->suggestion_id = null;
        $this->name = '';
        $this->keywords = '';
        $this->description = '';
        $this->locale = '';
        $this->path = '';
        $this->view = '';
    }
    
    public function store()
    {
        $this->validate([
            'name' => 'required|max:50',
            'keywords' => 'required|max:255',
            'description' => 'required|max:255',
            'locale' => 'nullable|string|in:en,it',
            'path' => 'nullable',
            'view' => 'nullable',
        ]);
    
        Suggestion::updateOrCreate(['id' => $this->suggestion_id], [
            'name' => $this->name,
            'keywords' => $this->keywords,
            'description' => $this->description,
            'locale' => $this->locale,
            'path' => $this->path,
            'view' => $this->view,
        ]);

        session()->flash('message', $this->suggestion_id ? 'Suggestion updated.' : 'Suggestion created.');

        $this->closeModal();
        $this->resetCreateForm();
    }

    public function edit($id)
    {
        $suggestion = Suggestion::findOrFail($id);
        $this->suggestion_id = $id;
        $this->name = $suggestion->name;
        $this->keywords = $suggestion->keywords;
        $this->description = $suggestion->description;
        $this->locale = $suggestion->locale;
        $this->path = $suggestion->path;
        $this->view = $suggestion->view;
    
        $this->openModal();
    }
    
    public function delete($id)
    {
    	$this->suggestion_id = $id;
        Suggestion::find($id)->delete();
        session()->flash('message', 'Suggestion deleted.');
    }
}
