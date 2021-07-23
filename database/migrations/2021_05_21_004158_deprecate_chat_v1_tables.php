<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeprecateChatV1Tables extends Migration
{
    public function up()
    {
        Schema::rename('messages', 'z_deprecated_messages');
        Schema::rename('chatthreads', 'z_deprecated_chatthreads');
        Schema::rename('list_user', 'z_deprecated_list_user');
        Schema::rename('lists', 'z_deprecated_lists');
    }

    public function down()
    {
        Schema::rename('z_deprecated_lists', 'lists');
        Schema::rename('z_deprecated_list_user', 'list_user');
        Schema::rename('z_deprecated_chatthreads', 'chatthreads');
        Schema::rename('z_deprecated_messages', 'messages');
    }
}
