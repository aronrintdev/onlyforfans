<?php

namespace Database\Seeders;

use App\Models\Financial\SegpayCard;
use App\Models\User;

class PaymentMethodsSeeder extends Seeder
{
    use SeederTraits;

    protected static $MIN = 1;
    protected static $MAX = 3;

    public function run()
    {
        $this->initSeederTraits('PaymentMethodsSeeder');

        $iter = 1;
        User::cursor()->each(function($user) use (&$iter) {

            $count = $this->faker->numberBetween(self::$MIN, self::$MAX);

            if ($this->appEnv !== 'testing') {
                $this->output->writeln("  - Creating $count payment methods for user " . $user->name . " (iter: $iter)");
            }

            for ($i = 0; $i <= $count; $i++) {
                SegpayCard::factory()->forUser($user)->create();
            }

            $iter++;
        });
    }
}