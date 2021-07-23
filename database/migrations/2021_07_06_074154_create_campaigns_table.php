<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promotional campaigns for users
 */
class CreateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('creator_id')->comment('The user who creates the promotional campaign');
            $table->foreign('creator_id')->references('id')->on('users');

            $table->enum('type', ['discount', 'trial']);
            $table->boolean('has_new')->nullable();
            $table->boolean('has_expired')->nullable();
            $table->integer('subscriber_count')->comment('-1 means No Limit');
            $table->unsignedInteger('offer_days');
            $table->unsignedInteger('discount_percent')->nullable();
            $table->unsignedInteger('trial_days')->nullable();
            $table->string('message')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
