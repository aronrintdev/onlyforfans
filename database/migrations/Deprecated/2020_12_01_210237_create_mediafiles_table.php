<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediafilesTable extends Migration
{
    public function up()
    {
        Schema::create('mediafiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid')->unique();
            $table->string('slug')->unique();
            $table->string('filename')->nullable()->comment('Filename as stored, in S3 for ex')->unique();

            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype',63)->comment('MediaFile Type: Enumeration');

            $table->string('mimetype',255)->nullable();
            $table->string('orig_ext',15)->nullable();
            $table->string('orig_filename',511)->nullable();
            $table->string('resource_type',255)->nullable()->comment('Polymorhic relation type');
            $table->unsignedInteger('resource_id')->nullable()->comment('Polymorphic relation FKID');
            $table->longtext('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->longtext('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediafiles');
    }
}
