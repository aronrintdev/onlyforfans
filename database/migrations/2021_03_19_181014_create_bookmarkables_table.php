<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookmarkablesTable extends Migration
{
    public function up()
    {
        Schema::create('bookmarkables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->comment('User that is bookmarking the resource');
            $table->nullableUuidMorphs('bookmarkable');
            $table->unique(['user_id', 'bookmarkable_id']);
            $table->json('cattrs')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookmarkables');
    }
}
