<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMessagesTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['receiver_id', 'user_id', 'message', 'receiver_name', 'media_id']);
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->text('mcontent')->nullable();
            $table->nullableUuidMorphs('messagable');
            $table->integer('mcounter')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['mcontent', 'mcounter', 'messagable_id', 'messagable_type']);
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->string('receiver_name')->nullable();
            $table->uuid('receiver_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->text('message')->nullable();
            $table->uuid('media_id')->nullable();
        });
    }
}
