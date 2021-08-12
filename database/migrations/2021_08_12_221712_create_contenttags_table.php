<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContenttagsTable extends Migration
{
    public function up()
    {
        Schema::create('contenttags', function (Blueprint $table) {
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG
            $table->string('ctag', 127);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contenttags');
    }
}
