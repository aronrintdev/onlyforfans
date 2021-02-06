<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikeablesTable extends Migration
{
    public function up()
    {
        Schema::create('likeables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('likee_id')->comment('User who is doing the liking');
            $table->foreign('likee_id')->references('id')->on('users');
            $table->string('likeable_type',255)->comment('Polymorhic relation type of resource being liked');
            $table->unsignedInteger('likeable_id')->comment('Polymorphic relation FKID of resource being liked');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('likeables');
    }
}
