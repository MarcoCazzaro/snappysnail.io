<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use App\Models\Scopes\SuggestionsSortingScope;
use App\Traits\HasImages;

#[ScopedBy([SuggestionsSortingScope::class])]
class Suggestion extends Model
{
    use HasImages;
    protected $fillable = ['title', 'keywords', 'description', 'url', 'locale', 'sorting'];
}
