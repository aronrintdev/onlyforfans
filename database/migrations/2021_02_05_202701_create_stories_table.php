<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timeline_id')->nullable();
            $table->string('stype')->comment('Enum: Story type');
            $table->json('content')->nullable()->comment('JSON-encoded content attributes');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded metadata');
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
        Schema::dropIfExists('stories');
    }
}
