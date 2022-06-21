<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Suggestion;

class SnailSearch extends Component
{
	public $searchTerms;

    public function render()
    {
        $no_results = false;
        $too_short = false;
    	$searchTerms = '%' . $this->searchTerms . '%';
    	$suggestions = Suggestion::where('keywords', 'like' , $searchTerms );
    	if (strlen($searchTerms) < 5) {
    		$suggestions->whereRaw("0=1");
            if (strlen($searchTerms) > 0) {
                $too_short = true;
            }
    	}
        $suggestions->orderByRaw('CASE WHEN id=4 THEN 0 ELSE id END ASC');
    	$suggestions = $suggestions->get();
        if ($suggestions->count() === 0 && strlen($searchTerms) >= 5) {
            $no_results = true;
        }
        return view('livewire.snail-search', compact('suggestions', 'no_results', 'too_short'));
    }
}
