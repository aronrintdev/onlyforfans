<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipPriceToChatthreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatthreads', function (Blueprint $table) {
            //
            $table->integer('tip_price')->nullable();
            $table->boolean('paid')->default(false);
            $table->boolean('is_unread')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatthreads', function (Blueprint $table) {
            //
            $table->dropColumn(['tip_price', 'paid', 'is_unread']);
        });
    }
}
