<?php

namespace App\Models;

use App\Models\Scopes\SuggestionsSortingScope;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[ScopedBy([SuggestionsSortingScope::class])]
class Suggestion extends Model
{
    use HasFactory, HasImages {
        HasImages::images as private ownImages;
        HasImages::latestImage as private ownLatestImage;
        HasImages::oldestImage as private ownOldestImage;
    }

    protected $fillable = ['key', 'title', 'keywords', 'description', 'url', 'locale', 'sorting', 'translation_of'];

    public function translationSource(): BelongsTo
    {
        return $this->belongsTo(Suggestion::class, 'translation_of');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Suggestion::class, 'translation_of');
    }

    /**
     * A translation has no images of its own — it shares its English source's
     * rows by delegating this relation, rather than holding independent copies
     * that could (and repeatedly did — see docs/IMAGES_OPTIMISATION.md §2)
     * drift out of sync with them.
     */
    public function images(): MorphMany
    {
        return $this->translation_of !== null
            ? $this->translationSource->images()
            : $this->ownImages();
    }

    public function latestImage(): MorphOne
    {
        return $this->translation_of !== null
            ? $this->translationSource->latestImage()
            : $this->ownLatestImage();
    }

    public function oldestImage(): MorphOne
    {
        return $this->translation_of !== null
            ? $this->translationSource->oldestImage()
            : $this->ownOldestImage();
    }
}
