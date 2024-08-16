<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoPopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_popups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64);
            $table->text('video');
            $table->char('is_active', 255)->default('0');
            $table->timestamps();
            $table->text('thumbnail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_popups');
    }
}
