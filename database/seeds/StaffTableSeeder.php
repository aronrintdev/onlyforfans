<?php
namespace Database\Seeders;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Libs\FactoryHelpers;
use App\Models\Staff;
use App\Models\User;

class StaffTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('StaffTableSeeder'); // $this->{output, faker, appEnv}

        if ( $this->appEnv === 'testing' ) {
            $this->output->writeln("  - Creating managers and staff members for testing...");
        }

        $users = User::get();

        $bin = collect();
        $users->each( function($u) use(&$users, &$bin) {
            static $iter = 1;

            $t = $this->faker->randomElement([
                'manager',
                'member',
                'owner',
                'none',
                'none',
                'none',
            ]);
            switch ($t) {
                case 'manager',
                    break;
                case 'member',
                    break;
                case 'owner',
                    break;
            }
        }
        /*
            pending
            active
            manager
            member
            registered invite
            guest invite
         */



            $sessionsUser = $users[0];
            // Creating a pending manager
            $pendingManager = $users[1];
            Staff::create([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $pendingManager->email,
                'role' => 'manager',
                'owner_id' => $sessionsUser->id,
                'token' => str_random(60),
                'creator_id' => null,
                'user_id' => null,
            ]);
            // Make an active manager
            $activeManager = $users[2];
            Staff::create([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $activeManager->email,
                'role' => 'manager',
                'owner_id' => $sessionsUser->id,
                'token' => str_random(60),
                'creator_id' => null,
                'active' => true,
                'pending' => false,
                'user_id' => $activeManager->id,
            ]);

            // Creating a pending staff member of the above active manager
            $pendingStaff = $users[3];
            Staff::create([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $pendingStaff->email,
                'role' => 'staff',
                'owner_id' => $activeManager->id,
                'token' => str_random(60),
                'creator_id' => $sessionsUser->id,
                'user_id' => null,
            ]);

            // Making an active staff member
            $activeStaff = $users[4];
            Staff::create([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $activeStaff->email,
                'role' => 'staff',
                'owner_id' => $activeManager->id,
                'token' => str_random(60),
                'creator_id' => $sessionsUser->id,
                'active' => true,
                'pending' => false,
                'user_id' => $activeStaff->id,
            ]);
    }
}
