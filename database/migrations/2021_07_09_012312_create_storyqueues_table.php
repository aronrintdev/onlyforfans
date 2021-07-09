<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoryqueuesTable extends Migration
{
    public function up()
    {
        Schema::create('storyqueues', function (Blueprint $table) {
            $table->increments('id'); // just use integer as this is a join table and sync, etc may not work with UUID %PSG (eg, if we need to use query builder insert instead of Eloquent)

            $table->uuid('story_id')->comment("FK - Story which log entry applies to");
            $table->foreign('story_id')->references('id')->on('stories');

            $table->uuid('timeline_id')->comment("FK - Timeline (epic) which log entry applies to");
            $table->foreign('timeline_id')->references('id')->on('timelines');

            $table->uuid('viewer_id')->comment("FK - User who is has/will view the story");
            $table->foreign('viewer_id')->references('id')->on('users');

            $table->dateTime('viewed_at')->nullable()->comment("Date & time story has been viewed by viewer, if not yet viewed then NULL");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('storyqueues');
    }
}
