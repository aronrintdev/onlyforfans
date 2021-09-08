<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegpayWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->create('segpay_websites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timeline_id')->nullable();

            // This is from their documentation, note that the Pascal case is what they want sent to them
            //   we will do conversation when sending
            $table->integer('base_approved_id')->nullable();
            $table->string('url', 150)->nullable(); // max length is 150 according to documentation
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('access_notes')->nullable();
            $table->string('support_email', 50)->nullable(); // max length is 50 according to documentation
            $table->string('tech_email', 50)->nullable();    // max length is 50 according to documentation
            $table->string('faq_link', 150)->nullable();     // max length is 150 according to documentation
            $table->string('help_link', 150)->nullable();    // max length is 150 according to documentation

            $table->string('sent_to')->nullable();        // API url that request was sent to
            $table->timestamp('sent_at')->nullable();     // When the request was sent to SegPay
            $table->timestamp('failed_at')->nullable();   // If API call fails
            $table->integer('response_code')->nullable(); // The response code that segpay gave back
            $table->json('response_body'); // The response that segpay gave back
            $table->json('notes');
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
        Schema::connection('financial')->dropIfExists('segpay_websites');
    }
}
