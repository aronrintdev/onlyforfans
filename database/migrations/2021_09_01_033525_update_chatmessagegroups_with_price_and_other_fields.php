<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChatmessagegroupsWithPriceAndOtherFields extends Migration
{
    public function up()
    {
        Schema::table('chatmessagegroups', function (Blueprint $table) {
            $table->boolean('purchase_only')->default(false)->after('sender_id');
            $table->bigInteger('price')->default(0)->after('sender_id');
            $table->string('currency', 3)->default('USD')->after('sender_id');
            $table->longtext('mcontent')->after('sender_id');
        });
    }

    public function down()
    {
        Schema::table('chatmessagegroups', function (Blueprint $table) {
            $table->dropColumn('purchase_only');
            $table->dropColumn('price');
            $table->dropColumn('currency');
            $table->dropColumn('mcontent');
        });
    }
}
