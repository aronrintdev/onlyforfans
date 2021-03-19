<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('name', 250);
            $table->text('about');
            $table->uuid('avatar_id')->nullable();
            $table->uuid('cover_id')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('is_follow_for_free')->default(false);
            $table->unsignedInteger('price')->nullable()->default(0);
            $table->string('currency', 4)->nullable()->default('USD');
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
        Schema::dropIfExists('timelines');
    }
}
