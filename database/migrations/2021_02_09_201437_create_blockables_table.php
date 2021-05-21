<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockablesTable extends Migration
{
    public function up()
    {
        Schema::create('blockables', function (Blueprint $table) {
            /**
             * User blocking resource
             */
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG
            $table->uuid('user_id')->comment('User blocking resource');
            $table->foreign('user_id')->references('id')->on('users');

            $table->uuidMorphs('blockable');

            $table->index(['user_id', 'blockable_id']);

            $table->json('cattrs')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blockables');
    }
}
