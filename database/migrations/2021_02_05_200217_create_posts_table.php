<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->uuid('user_id');
            $table->uuidMorphs('postable');
            $table->text('description');
            $table->boolean('active');
            $table->string('type');
            $table->unsignedInteger('price')->default(0);
            $table->string('currency')->default('USD');
            $table->integer('schedule_datetime')->nullable()->comment('Optional date-time at which to publish the post');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
