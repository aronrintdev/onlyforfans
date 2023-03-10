<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatmessagesV2Table extends Migration
{
    // wait, how to support group chats (?)
    public function up()
    {
        Schema::create('chatmessages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('chatthread_id');
            $table->foreign('chatthread_id')->references('id')->on('chatthreads');

            $table->uuid('sender_id');
            $table->foreign('sender_id')->references('id')->on('users');

            $table->longtext('mcontent');
            $table->datetime('deliver_at')->nullable()->comment('If non-null, message is not delivered until date provided');
            $table->boolean('is_delivered')->default(true);
            $table->boolean('is_read')->default(false);
            $table->boolean('is_flagged')->default(false)->comment('True if message was flagged by *receiver*, details in cattrs');

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatmessages');
    }
}
