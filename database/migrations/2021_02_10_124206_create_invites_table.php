<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('itype', 63);
            $table->uuid('inviter_id');
            $table->foreign('inviter_id')->references('id')->on('users');
            $table->text('token');
            $table->json('cattrs');
            $table->json('meta');
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
        Schema::dropIfExists('invites');
    }
}
