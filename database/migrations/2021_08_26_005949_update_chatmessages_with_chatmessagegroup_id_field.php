<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChatmessagesWithChatmessagegroupIdField extends Migration
{
    public function up()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->uuid('chatmessagegroup_id')->after('chatthread_id')->nullable()->comment("FKID to [chatmessagegroups]");
            $table->foreign('chatmessagegroup_id')->references('id')->on('chatmessagegroups');
        });
    }

    public function down()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->dropForeign('chatmessages_chatmessagegroup_id_foreign');
            $table->dropColumn('chatmessagegroup_id');
        });
    }
}
