<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikeablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likeables', function (Blueprint $table) {
            //$table->uuid('id')->primary();
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG
            $table->uuid('likee_id')->comment('User who is doing the liking');
            $table->foreign('likee_id')->references('id')->on('users');
            $table->uuidMorphs('likeable');
            $table->index(['likee_id', 'likeable_id']);
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
        Schema::dropIfExists('likeables');
    }
}
