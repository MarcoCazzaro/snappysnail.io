<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->morphs('imageable');
            $table->string('caption')->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail_file_path')->nullable();
            $table->dateTime('optimised_at')->nullable()->index();
        });
        // Local convenience: clear image files left over from a previous database
        // so `migrate:fresh --seed` starts clean. Never while testing — the test
        // suite runs migrations but shares this directory with local dev.
        if (! app()->environment('production') && ! app()->runningUnitTests()) {
            (new Filesystem)->cleanDirectory(storage_path('app/public/suggestions/images'));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
