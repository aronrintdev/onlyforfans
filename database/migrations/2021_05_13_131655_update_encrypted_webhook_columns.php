<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEncryptedWebhookColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webhooks', function (Blueprint $table) {
            $table->text('headers')->nullable()->change();
        });
        Schema::table('webhooks', function (Blueprint $table) {
            $table->text('body')->nullable()->change();
        });
        Schema::table('webhooks', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webhooks', function (Blueprint $table) {
            $table->json('headers')->nullable()->change();
        });
        Schema::table('webhooks', function (Blueprint $table) {
            $table->json('body')->nullable()->change();
        });
        Schema::table('webhooks', function (Blueprint $table) {
            $table->json('notes')->nullable()->change();
        });
    }
}
