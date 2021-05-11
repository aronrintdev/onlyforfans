<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->create('payout_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->enum('type', [ 'affiliates', 'payouts' ]);

            /** The generated contents of the csv file */
            $table->json('csv');

            /** Admin notes about this batch */
            $table->json('notes');

            /** Admin user that is assigned to handle this batch */
            $table->uuid('assigned_to')->nullable();

            /** Admin user that marked the payout as settled */
            $table->uuid('settled_by')->nullable();

            /** When the batch was marked as settled. */
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->dropIfExists('payout_batches');
    }
}
