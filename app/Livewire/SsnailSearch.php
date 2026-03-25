<?php

namespace App\Livewire;

use App\Models\Suggestion;
use Livewire\Component;

class SsnailSearch extends Component
{
    public $searchTerms;

    public function render()
    {
        $suggestions = null;
        $no_results = false;
        $too_short = false;
        $keywords = [];
        $searchTerms = is_string($this->searchTerms) ? $this->searchTerms : '';
        switch (true) {
            case strlen($searchTerms) === 0:
                break;

            case (strlen($searchTerms) < 3) && (strtolower($searchTerms) !== 'cv'):
                $too_short = true;
                break;

            default:
                $searchTerms = '%'.$searchTerms.'%';
                $suggestions = Suggestion::where('locale', app()->getLocale())->where('keywords', 'like', $searchTerms);
                //$suggestions->orderByRaw('CASE WHEN id=4 THEN 0 ELSE id END ASC');
                $suggestions->orderBy('sorting', 'DESC');
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
        $this->dispatch('searchUpdated', array_unique($keywords));

        return view('livewire.ssnail-search', compact('suggestions', 'no_results', 'too_short'));
    }
}
