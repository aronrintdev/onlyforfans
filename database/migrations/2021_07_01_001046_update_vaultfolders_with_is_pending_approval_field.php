<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVaultfoldersWithIsPendingApprovalField extends Migration
{
    public function up()
    {
        Schema::table('vaultfolders', function (Blueprint $table) {
            $table->boolean('is_pending_approval')->default(false)->after('vault_id');
        });
    }

    public function down()
    {
        Schema::table('vaultfolders', function (Blueprint $table) {
            $table->dropColumn('is_pending_approval');
        });
    }
}
