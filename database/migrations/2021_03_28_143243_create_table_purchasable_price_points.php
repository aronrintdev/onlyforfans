<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePurchasablePricePoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasable_price_points', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('purchasable');
            $table->bigInteger('price');
            $table->string('currency', 3);

            // Indicates price point is the current active default
            $table->boolean('current')->default(false);

            // Indicates price point can be used
            $table->boolean('active')->default(false);

            // When active status will expire
            $table->timestamp('expires')->nullable();

            // Promo Code ability
            $table->string('available_with')->nullable();

            // The access level this price point will give
            $table->string('access_level')->default('default');

            $table->json('custom_attributes')->nullable();
            $table->json('metadata')->nullable();

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
        Schema::dropIfExists('purchasable_price_points');
    }
}
