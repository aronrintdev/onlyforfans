<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareablesTable extends Migration
{
    public function up()
    {
        Schema::create('shareables', function (Blueprint $table) {
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG
            //$table->uuid('user_id')->nullable()->comment('User that is sharing the resource'); // removed as doesn't seem to be used %PSG 20210519
            $table->uuid('sharee_id')->nullable()->comment('User with whom resource is being shared with');
            $table->nullableUuidMorphs('shareable');

            $table->index(['sharee_id', 'shareable_id']);

            $table->boolean('is_approved')->default(true);
            $table->string('access_level', 63)->default('default');
            $table->json('cattrs')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shareables');
    }
}
