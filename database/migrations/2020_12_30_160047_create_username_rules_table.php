<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsernameRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('username_rules', function (Blueprint $table) {
            $table->increments('id');

            // Word or regex
            $table->string('rule', 255);

            // Normally want blacklist in here, but setting up for whitelisting in the future.
            //   Approval will be for rules that require admin approval
            $table->enum('type', ['blacklist', 'whitelist', 'approval'])->default('blacklist');

            // Comparison type for evaluation.
            $table->enum('comparison_type', ['word', 'regex']);

            // Common language explanation for blacklist reason if necessary.
            //   So we can return more detailed information to users.
            $table->string('explanation')->nullable();

            // What Admin added the rule, nullable for system added rules.
            $table->integer('added_by')->nullable();
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
        Schema::dropIfExists('username_rules');
    }
}
