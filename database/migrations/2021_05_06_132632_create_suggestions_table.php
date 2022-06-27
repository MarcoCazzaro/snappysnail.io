<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 50)->index();
            $table->string('keywords', 255)->index();
            $table->string('description', 255)->index();
            $table->string('locale', 5)->index()->default('en');
            $table->string('path', 255)->nullable()->default(null);
            $table->string('view', 255)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suggestions');
    }
}
