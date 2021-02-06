<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocked_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('blocked_by');
            $table->string('ip_address');
            $table->string('country')->nullable();
            $table->timestamps();
            
            $table->foreign('blocked_by')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blocked_profiles');
    }
}
