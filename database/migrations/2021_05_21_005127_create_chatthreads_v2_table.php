<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatthreadsV2Table extends Migration
{
    public function up()
    {
        Schema::create('chatthreads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->boolean('is_tip_required')->default(false)->comment("True if sender must tip in order for thread's message to be delivered to receiver");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatthreads');
    }
}
