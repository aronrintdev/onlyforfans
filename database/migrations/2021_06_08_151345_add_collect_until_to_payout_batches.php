<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollectUntilToPayoutBatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->timestamp('collect_until')->nullable()->after('type');
        });
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->json('csv')->nullable()->change();
        });
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->json('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->dropColumn('collect_until');
        });
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->json('csv')->nullable(false)->change();
        });
        Schema::connection('financial')->table('payout_batches', function (Blueprint $table) {
            $table->json('notes')->nullable(false)->change();
        });
    }
}
