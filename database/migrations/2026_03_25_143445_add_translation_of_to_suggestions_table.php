<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->foreignId('translation_of')
                ->nullable()
                ->after('sorting')
                ->constrained('suggestions')
                ->nullOnDelete();
        });

        // Backfill same-title pairs (database-agnostic)
        $suggestions = DB::table('suggestions')
            ->whereNull('translation_of')
            ->orderBy('id')
            ->get(['id', 'title', 'locale']);

        foreach ($suggestions->groupBy('title') as $group) {
            if ($group->count() < 2) {
                continue;
            }

            $en = $group->firstWhere('locale', 'en');
            $it = $group->firstWhere('locale', 'it');

            if ($en && $it) {
                DB::table('suggestions')
                    ->where('id', $it->id)
                    ->update(['translation_of' => $en->id]);
            }
        }

        // Backfill the 3 suggestions whose titles were manually translated
        $specialPairs = [
            ['it_title' => 'Contatti',     'en_title' => 'Contact'],
            ['it_title' => 'Servizi',      'en_title' => 'Services'],
            ['it_title' => 'Questo sito',  'en_title' => 'This website'],
        ];

        foreach ($specialPairs as $pair) {
            $source = DB::table('suggestions')
                ->where('title', $pair['en_title'])
                ->where('locale', 'en')
                ->first();

            $target = DB::table('suggestions')
                ->where('title', $pair['it_title'])
                ->where('locale', 'it')
                ->whereNull('translation_of')
                ->first();

            if ($source && $target) {
                DB::table('suggestions')
                    ->where('id', $target->id)
                    ->update(['translation_of' => $source->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropForeign(['translation_of']);
            $table->dropColumn('translation_of');
        });
    }
};
