<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContenttagsTable extends Migration
{
    public function up()
    {
        Schema::create('contenttags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ctag', 127);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contenttags');
    }
}
