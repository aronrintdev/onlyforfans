<?php

namespace Database\Seeders;

use App\Models\Financial\AchAccount;
use App\Models\User;

/**
 *
 * @package Database\Seeders
 */
class AchAccountSeeder extends Seeder
{
    use SeederTraits;

    protected static $MIN = 1;
    protected static $MAX = 3;

    public function run()
    {
        $this->initSeederTraits('PaymentMethodsSeeder');

        $userCount = User::count();
        $iter = 1;
        User::cursor()->each(function($user) use (&$iter, $userCount) {

            $count = $this->faker->numberBetween(self::$MIN, self::$MAX);

            if ($this->appEnv !== 'testing') {
                $this->output->writeln(" - ( {$iter} / {$userCount} ) - Creating {$count} ach accounts for user {$user->username}");
            }

            AchAccount::factory($count)->forUser($user)->create();

            $iter++;
        });
    }
}
