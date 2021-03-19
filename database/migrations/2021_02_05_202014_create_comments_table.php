<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('commentable');
            $table->uuid('parent_id')->nullable();
            $table->uuid('user_id');
            $table->text('description');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
