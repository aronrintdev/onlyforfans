<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPricesForFollowersAndSubscribersToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->unsignedInteger('price_for_followers')->default(0);
            $table->unsignedInteger('price_for_subscribers')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['price_for_followers', 'price_for_subscribers']);
            $table->unsignedInteger('price')->default(0);
        });
    }
}
