<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContenttaggablesJoinTable extends Migration
{
    public function up()
    {
        Schema::create('contenttaggables', function (Blueprint $table) {
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG

            $table->uuid('contenttag_id')->comment("Tag with associated with the 'contenttaggable' resource");
            $table->foreign('contenttag_id')->references('id')->on('contenttags');

            $table->uuidMorphs('contenttaggable');

            $table->index(['contenttag_id', 'contenttaggable_id']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contenttaggables');
    }
}
