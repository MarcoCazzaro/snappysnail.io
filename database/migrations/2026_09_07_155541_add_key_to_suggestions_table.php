<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Stable slug shared by every locale of one suggestion. This is the identity
     * the seeder upserts against, so it must not depend on auto-increment ids.
     *
     * @var array<string, string>
     */
    private array $keysByTitle = [
        'Contact' => 'contact',
        'Contatti' => 'contact',
        'Curriculum' => 'curriculum',
        'Services' => 'services',
        'Servizi' => 'services',
        'This website' => 'this-website',
        'Questo sito' => 'this-website',
        'Gooruf.com' => 'gooruf-com',
        'Forbes (2021-2022)' => 'forbes-2021-2022',
        'Forbes (2021)' => 'forbes-2021-2022',
        'Debora Antonello' => 'debora-antonello',
        'Osteopata Piazza' => 'osteopata-piazza',
        'Equos.it' => 'equos-it',
        'Camping Europa' => 'camping-europa',
        'Indeeper.it' => 'indeeper-it',
        'The Most Famous Website In The World' => 'the-most-famous-website-in-the-world',
        'Bluerating.com' => 'bluerating-com',
        'Forbes (2023-2025)' => 'forbes-2023-2025',
        'Forbes (2023)' => 'forbes-2023-2025',
        'BetAll Bomber Bonus' => 'betall-bomber-bonus',
        'Pornobello.org' => 'pornobello-org',
        'Agenzy' => 'agenzy',
        'GMGreen.it' => 'gmgreen-it',
        'Mediakey.it' => 'mediakey-it',
        'TRIMaterials.com' => 'trimaterials-com',
        'NoleggioElettrico.com' => 'noleggioelettrico-com',
        'Movimento Metropolitano' => 'movimento-metropolitano',
        'Bertone Design' => 'bertone-design',
    ];

    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('id');
        });

        $this->backfillKeys();

        Schema::table('suggestions', function (Blueprint $table) {
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropUnique(['key', 'locale']);
            $table->dropColumn('key');
        });
    }

    /**
     * Backfill existing rows: map known titles to their slug, then let any
     * translation inherit its source's key, then slug whatever is left over.
     */
    private function backfillKeys(): void
    {
        foreach ($this->keysByTitle as $title => $key) {
            DB::table('suggestions')->where('title', $title)->update(['key' => $key]);
        }

        $rows = DB::table('suggestions')->get(['id', 'title', 'locale', 'translation_of', 'key']);
        $keyById = $rows->pluck('key', 'id');

        foreach ($rows as $row) {
            if (! empty($row->key)) {
                continue;
            }

            $key = ! empty($row->translation_of) && ! empty($keyById[$row->translation_of])
                ? $keyById[$row->translation_of]
                : (Str::slug($row->title) ?: 'suggestion-'.$row->id);

            $suffix = 0;
            $candidate = $key;
            while (DB::table('suggestions')->where('key', $candidate)->where('locale', $row->locale)->exists()) {
                $candidate = $key.'-'.(++$suffix);
            }

            DB::table('suggestions')->where('id', $row->id)->update(['key' => $candidate]);
            $keyById[$row->id] = $candidate;
        }
    }
};
