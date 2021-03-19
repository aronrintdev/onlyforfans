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
            $table->uuid('user_id')->nullable()->comment('User that is sharing the resource');
            $table->uuid('sharee_id')->nullable()->comment('User with whom resource is being shared with');
            $table->nullableUuidMorphs('shareable');

            $table->index(['sharee_id', 'shareable_id']);

            $table->boolean('is_approved')->default(true);
            $table->string('access_level', 63)->default('default');
            $table->json('cattrs')->nullable();
            $table->json('meta')->nullable();
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
