<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvites extends Migration
{
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid')->unique();

            $table->string('email', 250);
            $table->string('itype',63)->comment('Invite Type: Enumeration');
            $table->unsignedInteger('inviter_id');
            $table->foreign('inviter_id')->references('id')->on('users');
            $table->text('token');

            $table->longtext('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->longtext('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
}
