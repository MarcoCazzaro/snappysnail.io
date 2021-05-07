<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Suggestion;

class SnailSearch extends Component
{
	public $searchTerms;

    public function render()
    {
    	$searchTerms = '%' . $this->searchTerms . '%';
    	$suggestions = Suggestion::where('keywords', 'like' , $searchTerms );
    	if ($searchTerms === "%%") {
    		$suggestions->whereRaw("0=1");
    	}
    	$suggestions = $suggestions->get();
        return view('livewire.snail-search', compact('suggestions'));
    }
}
