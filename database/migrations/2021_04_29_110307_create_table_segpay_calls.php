<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSegpayCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segpay_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();

            $table->json('query')->nullable();
            $table->json('params')->nullable();

            $table->bigInteger('amount')->nullable();
            $table->string('currency', 3)->nullable();

            $table->uuid('user_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->nullableMorphs('resource');

            $table->uuid('webhook_id')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->text('response')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

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
        Schema::dropIfExists('segpay_calls');
    }
}
