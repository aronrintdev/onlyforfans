<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeQueryToQueryItemsOnSegpayCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->json('query_items')->nullable()->after('method');
        });
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->dropColumn('query');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->json('query')->nullable()->after('method');
        });
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->dropColumn('query_items');
        });
    }
}
