<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tips', function (Blueprint $table) {
            // PK
            $table->uuid('id')->primary();

            // User sending the tip
            $table->uuid('sender_id');

            // User receding the tip
            $table->uuid('receiver_id');

            // Tippable Item (optional)
            $table->nullableUuidMorphs('tippable');

            // Financial Account
            $table->uuid('account_id')->nullable();

            // Message from sending user (optional)
            $table->text('message')->nullable();

            // Tip Amount
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('amount');

            // Reoccurring tips data. Defaults to single time (single)
            $table->string('period')->default('single');
            $table->integer('period_interval')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('manual_charge')->default(false);
            // Id of last Transaction
            $table->uuid('last_transaction_id')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();

            $table->json('custom_attributes');
            $table->json('metadata');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tips');
    }
}
