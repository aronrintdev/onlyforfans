<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//use App\Enums;

class CreateStoriesTable extends Migration
{
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('timeline_id')->nullable()->comment('FKID');
            $table->longtext('content')->nullable()->comment('JSON-encoded attributes');
            $table->longtext('cattrs')->nullable()->comment('JSON-encoded attributes');
            $table->string('stype')->comment('Enum: Story type');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('stories', function (Blueprint $table) {
            Schema::drop('stories');
        });
    }
}
