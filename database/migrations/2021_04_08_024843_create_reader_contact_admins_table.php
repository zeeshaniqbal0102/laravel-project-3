<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReaderContactAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reader_contact_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reader_id')->index('reader_fk_reader_contact_admin_reader_id');
            $table->string('title', 255);
            $table->text('message');
            $table->integer('state')->default(1);
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
        Schema::dropIfExists('reader_contact_admins');
    }
}
