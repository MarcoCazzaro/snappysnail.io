<?php

namespace App\Models;

use App\Models\Scopes\SuggestionsSortingScope;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([SuggestionsSortingScope::class])]
class Suggestion extends Model
{
    use HasFactory, HasImages;

    protected $fillable = ['title', 'keywords', 'description', 'url', 'locale', 'sorting'];
}
