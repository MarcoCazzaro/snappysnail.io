<?php

namespace App\Models;

use App\Models\Scopes\SuggestionsSortingScope;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([SuggestionsSortingScope::class])]
class Suggestion extends Model
{
    use HasFactory, HasImages;

    protected $fillable = ['title', 'keywords', 'description', 'url', 'locale', 'sorting', 'translation_of'];

    public function translationSource(): BelongsTo
    {
        return $this->belongsTo(Suggestion::class, 'translation_of');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Suggestion::class, 'translation_of');
    }
}
