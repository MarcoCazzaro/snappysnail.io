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
        $suggestions = false;
        $keywords = [];
        switch (true) {
            case (strlen($this->searchTerms) === 0):
                break;

            case (strlen($this->searchTerms) < 3) && (strtolower($this->searchTerms) !== 'cv'):
                $too_short = true;
                break;

            default:
                $searchTerms = '%' . $this->searchTerms . '%';
                $suggestions = Suggestion::where('keywords', 'like', $searchTerms);
                //$suggestions->orderByRaw('CASE WHEN id=4 THEN 0 ELSE id END ASC');
                $suggestions->orderBy('sorting', 'asc');
                $suggestions = $suggestions->get();
                if ($suggestions->count() === 0) {
                    $no_results = true;
                }
                if ($suggestions && $suggestions->count() > 0) {
                    foreach ($suggestions as $suggestion) {
                        $keywords = array_merge($keywords, explode(',', $suggestion->keywords));
                    }
                }
                break;
        }
        $this->emit('searchUpdated', array_unique($keywords));
        return view('livewire.snail-search', compact('suggestions', 'no_results', 'too_short'));
    }
}
