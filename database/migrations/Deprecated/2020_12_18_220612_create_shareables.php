<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShareables extends Migration
{
    public function up()
    {
        Schema::create('shareables', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('sharee_id')->nullable()->comment('User with whom resource is being shared with');
            $table->string('shareable_type',255)->nullable()->comment('Polymorhic relation type of resource being shared');
            $table->unsignedInteger('shareable_id')->nullable()->comment('Polymorphic relation FKID of resource being shared');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shareables');
    }
}
