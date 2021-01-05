<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            PostsTableSeeder::class,
            StoriesTableSeeder::class,
        );
    }

    private function oldSet() 
    {
        if (Config::get('app.env') == 'demo' || Config::get('app.env') == 'local') {
            $this->call(MediumTableSeeder::class);
            $this->call(CategoriesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(SettingsTableSeeder::class);
            $this->call(TimelinesTableSeeder::class);
            $this->call(HashtagsTableSeeder::class);
            $this->call(AnnouncementsTableSeeder::class);
            $this->call(PostsTableSeeder::class);
            $this->call(CommentsTableSeeder::class);
            $this->call(NotificationsTableSeeder::class);
            $this->call(StaticpageTableSeeder::class);
            $this->call(AlbumsTableSeeder::class);
            $this->call(UsernameRulesSeeder::class);
        } else {
            $this->call(CategoriesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(StaticpageTableSeeder::class);
            $this->call(InstallerSeeder::class);
            $this->call(UsernameRulesSeeder::class);
        }
    }
}
