<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseOnlyColumnToChatmessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->string('currency', 3)->after('mcontent')->default('USD');
            $table->bigInteger('price')->after('mcontent')->default(0);
            $table->boolean('purchase_only')->after('mcontent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->dropColumn(['purchase_only', 'price', 'currency']);
        });
    }
}
