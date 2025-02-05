<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title', 255)->index();
            $table->string('keywords', 255)->index();
            $table->text('description')->nullable()->default(null);
            $table->string('url', 255)->nullable()->index();
            $table->string('locale', 5)->index()->default('en');
            $table->integer('sorting')->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
