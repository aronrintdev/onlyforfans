<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasedPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchased_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('purchased_by');
            $table->integer('post_id');
            $table->double('amount')->nullable();
            $table->string('stripe_id');
            $table->text('meta')->nullable();
            $table->string('payment_status');
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
        Schema::dropIfExists('purchased_posts');
    }
}
