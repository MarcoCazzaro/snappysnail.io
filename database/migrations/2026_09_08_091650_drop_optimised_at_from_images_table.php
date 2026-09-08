<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every Image row is now optimised synchronously at creation time by the
     * single ImageOptimisationContract call site, so nothing ever ends up
     * "un-optimised" and the column no longer carries information.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropIndex(['optimised_at']);
            $table->dropColumn('optimised_at');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dateTime('optimised_at')->nullable()->index();
        });
    }
};
