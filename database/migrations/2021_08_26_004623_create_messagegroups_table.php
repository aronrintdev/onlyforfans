<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\MessagegroupTypeEnum;

class CreateMessagegroupsTable extends Migration
{
    public function up()
    {
        Schema::create('chatmessagegroups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('mgtype')->default(MessagegroupTypeEnum::MASSMSG)->comment('Message Group type (enum)');
            $table->uuid('sender_id')->comment("FKID to [users], identifying owner of the group");
            $table->foreign('sender_id')->references('id')->on('users');
            //$table->uuid('chatmessage_id')->comment("FKID to [chatmessages]");
            //$table->foreign('chatmessage_id')->references('id')->on('chatmessages');

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatmessagegroups');
    }
}
