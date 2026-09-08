<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Suggestion::images() now delegates a translation to its English source's
     * images instead of holding its own duplicate rows that could drift out of
     * sync with them (see docs/IMAGES_OPTIMISATION.md §2/§3 for the bugs that
     * came from that drift). Every Image row directly owned by a translation
     * is now dead weight — correct copies are redundant, and copies broken by
     * the earlier backfill bug are actively wrong — since the translation
     * renders its source's images via the new relation regardless. Deleting
     * these rows does not touch any physical file: every file they reference
     * is still (or was already) referenced by the source's own row, or no
     * longer exists on disk at all.
     */
    public function up(): void
    {
        DB::table('images')
            ->where('imageable_type', 'App\\Models\\Suggestion')
            ->whereIn('imageable_id', function ($query) {
                $query->select('id')->from('suggestions')->whereNotNull('translation_of');
            })
            ->delete();
    }

    public function down(): void
    {
        // Not reversible: the deleted rows only ever duplicated data already
        // present, live, via the translation's source suggestion.
    }
};
