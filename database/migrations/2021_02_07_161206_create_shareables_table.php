<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shareables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user')->comment('User that is sharing the resource');
            $table->uuid('shared_with')->nullable()->comment('User with whom resource is being shared with');
            $table->nullableUuidMorphs('shareable');
            $table->boolean('is_approved')->default(true);
            $table->string('access_level', 63);
            $table->json('metadata')->nullable();
            $table->json('custom_attributes')->nullable();
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
        Schema::dropIfExists('shareables');
    }
}
