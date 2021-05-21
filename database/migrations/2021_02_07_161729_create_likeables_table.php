<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikeablesTable extends Migration
{
    public function up()
    {
        Schema::create('likeables', function (Blueprint $table) {
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG
            $table->uuid('liker_id')->comment('User who is doing the liking');
            $table->foreign('liker_id')->references('id')->on('users');
            $table->uuidMorphs('likeable');
            $table->index(['liker_id', 'likeable_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('likeables');
    }
}
