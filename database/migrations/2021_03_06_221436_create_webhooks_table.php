<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Type if know who webhook is from.
            $table->string('type')->nullable();

            // Calling origin
            $table->string('origin');

            // If the request was verified to be from the sender
            $table->boolean('verified')->default(false);

            // Request headers as json object
            $table->json('headers');

            // Body contents as json object
            $table->json('body');

            // Status of handling the webhook, unhandled useful for batching items later to not hold up response to
            //   sender
            $table->enum('status', ['unhandled', 'handled', 'ignored', 'error'])->default('unhandled');

            // Additional data where applicable
            $table->json('notes')->nullable();
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
        Schema::dropIfExists('webhooks');
    }
}
