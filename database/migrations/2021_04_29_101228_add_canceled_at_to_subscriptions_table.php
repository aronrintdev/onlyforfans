<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanceledAtToSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('canceled');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('canceled_at')->nullable()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('canceled_at');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('canceled')->default(false)->after('active');
        });
    }
}
