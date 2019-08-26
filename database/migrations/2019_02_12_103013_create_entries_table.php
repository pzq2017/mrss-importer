<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mrss_id');
            $table->string('guid', 100);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_type', 30)->nullable();
            $table->string('duration', 15)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('lang', 12)->nullable();
            $table->string('category', 300)->nullable();
            $table->string('keywords', 300)->nullable();
            $table->string('download_url');
            $table->string('thumbnail_url')->nullable();
            $table->string('status', 30)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entries');
    }
}
