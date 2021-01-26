<?php

namespace Database\Seeders;

use App\Notification;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Populate dummy Notifications
        Notification::factory()->count(40)->create();
    }
}
