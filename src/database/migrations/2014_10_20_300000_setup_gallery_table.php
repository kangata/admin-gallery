<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('Untitled');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->timestamps();
        });

        Schema::create('album_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->integer('category_id')->unsigned();
        });

        Schema::create('album_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->integer('tag_id')->unsigned();
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->text('slug');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('like')->default(0);
            $table->bigInteger('viewer')->default(0);
            $table->text('filename');
            $table->bigInteger('size')->comment('bytes');
            $table->timestamps();
        });

        Schema::create('photo_share', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('photo_id')->unsigned();
            $table->string('media');
            $table->bigInteger('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photo_share');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('album_tag');
        Schema::dropIfExists('album_category');
        Schema::dropIfExists('albums');
    }
}
