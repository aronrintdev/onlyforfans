<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediafilesTable extends Migration
{
    public function up()
    {
        Schema::create('mediafiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();

            $table->uuid('diskmediafile_id');
            $table->foreign('diskmediafile_id')->references('id')->on('diskmediafiles');

            $table->nullableUuidMorphs('resource');

            $table->boolean('is_primary')->default(false)->comment('True if this is the original mediafile associated with the disk image');
            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('Mediafile Type: Enumeration');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediafiles');
    }
}
