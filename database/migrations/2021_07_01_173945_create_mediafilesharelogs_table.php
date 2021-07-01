<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\MediafilesharelogStatusEnum;

class CreateMediafilesharelogsTable extends Migration
{
    public function up()
    {
        Schema::create('mediafilesharelogs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('sharer_id')->comment("user who is sharing (src)");
            $table->foreign('sharer_id')->references('id')->on('users');

            $table->uuid('sharee_id')->comment("user being shared to (dst)");
            $table->foreign('sharee_id')->references('id')->on('users');

            $table->uuid('srcmediafile_id')->comment("Orig. mediafile that is being shared (ie src of the ref being created)");
            $table->foreign('srcmediafile_id')->references('id')->on('mediafiles');

            // Nullable because will not be set until the receive approves the share, at which time the dst files are created
            $table->uuid('dstmediafile_id')->nullable()->comment("New mediafile that is being created to impl the share (ie dst ref itself)");
            $table->foreign('dstmediafile_id')->references('id')->on('mediafiles');

            // Make this nullable in case the receiver moves the files out of the orig. folder then deletes the folder
            $table->uuid('dstvaultfolder_id')->nullable()->comment("Share vaultfolder created to hold shared content (files) in sharee's vault");
            //$table->foreign('dstvaultfolder_id')->references('id')->on('vaultfolders'); // wont' let me do this ??

            $table->string('mfsl_status')->default(MediafilesharelogStatusEnum::PENDING);

            $table->json('cattrs')->nullable()->comment("JSON-encoded custom attributes");
            $table->json('meta')->nullable()->comment("JSON-encoded meta attributes");

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediafilesharelogs');
    }
}
