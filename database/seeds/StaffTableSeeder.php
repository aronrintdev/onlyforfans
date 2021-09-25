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

        $pools = [
            'creators' => collect(),
            'managers' => collect(),
            'members' => collect(),
        ];

        $users->each( function($u) use(&$users, &$pools) {
            $t = $this->faker->randomElement([
                'manager', 'manager',
                'member', 'member', 'member',
                'creator', 'creator', 'creator', 'creator', 'creator', 'creator',
                'none', 
            ]);
            switch ($t) {
                case 'manager':
                    $pools['managers']->push($u);
                    break;
                case 'member':
                    $pools['members']->push($u);
                    break;
                case 'creator':
                    $pools['creators']->push($u);
                    break;
            }
        });

        // --- creators inviting managers ---

        $pools['creators']->each( function($c) use(&$pools) {
            // invite a manager (registered user)
            $isToGuest = $this->faker->boolean();
            if ($isToGuest) {
                $fn = $this->faker->firstName;
                $ln = $this->faker->lastName;
                $attrs = [
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'email' => $fn.'.'.$ln.'@example.com',
                    'role' => 'manager',
                    'owner_id' => $c->id,
                    'creator_id' => $c->id,
                ];
            } else {
                $preManager = $pools['managers']->random();
                $attrs = [
                    'first_name' => $preManager->real_firstname ?? $this->name,
                    'last_name' => $preManager->real_lastname ?? $this->faker->lastName,
                    'email' => $preManager->email,
                    'role' => 'manager',
                    'owner_id' => $c->id,
                    'creator_id' => $c->id,
                    'user_id' => $preManager->id,
                ];
            }
            $staff = Staff::create($attrs);

            $isActive = $this->faker->boolean(63);
            if ($isActive) {
                $staff->activate();
                $staff->refresh();
            }
        });

        // --- managers inviting staff members ---

        $activeStaffAsManagers = Staff::where('role', 'manager')->where('active', true)->get();
        $activeStaffAsManagers->each( function($s) use(&$pools) {
            // invite a member (or 2)
            $isToGuest = $this->faker->boolean();
            if ($isToGuest) {
                $fn = $this->faker->firstName;
                $ln = $this->faker->lastName;
                $attrs = [
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'email' => $fn.'.'.$ln.'@example.com',
                    'role' => 'member',
                    'owner_id' => $s->owner_id,
                    'creator_id' => $s->creator_id,
                ];
            } else {
                $preMember = $pools['members']->random();
                $attrs = [
                    'first_name' => $preMember->real_firstname ?? $this->name,
                    'last_name' => $preMember->real_lastname ?? $this->faker->lastName,
                    'email' => $preMember->email,
                    'role' => 'member',
                    'owner_id' => $s->owner_id,
                    'creator_id' => $s->creator_id,
                    'user_id' => $preMember->id,
                ];
            }
            $staff = Staff::create($attrs);

            $isActive = $this->faker->boolean(63);
            if ($isActive) {
                $staff->activate();
                $staff->refresh();
            }
        });

    } // run()
}
