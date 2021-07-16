<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\VerifyServiceTypeEnum;
use App\Enums\VerifyStatusTypeEnum;

class CreateVerifyrequestsTable extends Migration
{
    public function up()
    {
        Schema::create('verifyrequests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('vservice')->nullable();
            $table->string('vstatus')->nullable()->comment('Can be null if we create record before sending request');

            $table->uuid('requester_id')->comment("FK - User who is has requested verification");
            $table->foreign('requester_id')->references('id')->on('users');

            $table->dateTime('last_checked_at')->nullable()->comment("Date & time status has been last polled for approval or denial");

            $table->text('callback_url')->nullable();
            $table->longtext('qrcode')->nullable();
            $table->longtext('notes')->nullable();

            $table->json('cattrs')->nullable()->comment("JSON-encoded custom attributes");
            $table->json('meta')->nullable()->comment("JSON-encoded meta attributes");

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('verifyrequests');
    }
}
