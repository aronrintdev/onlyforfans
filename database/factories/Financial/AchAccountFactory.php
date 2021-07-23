<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\AchAccountBankTypeEnum;
use App\Enums\Financial\AchAccountTypeEnum;
use App\Models\Financial\Account;
use App\Models\User;
use App\Models\Financial\AchAccount;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AchAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $defaultCurrency = Config::get('transactions.defaultCurrency', 'USD');

        return [
            'user_id' => User::factory(),
            'type' => AchAccountTypeEnum::INDIVIDUAL,
            'name' => $this->faker->numerify('Ach Account ###'),
            'residence_country' => 'US',
            'beneficiary_name' => $this->faker->name(),
            'bank_name' => $this->faker->company(),
            'routing_number' => '123456789',
            'account_number' => $this->faker->randomNumber(7),
            'account_type' => $this->faker->randomElement([
                AchAccountBankTypeEnum::CHECKING,
                AchAccountBankTypeEnum::SAVINGS,
            ]),
            'bank_country' => 'US',
            'currency' => $defaultCurrency,
            'metadata' => [ 'generated' => true ],
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (AchAccount $achAccount) {
            if ($achAccount->account()->exists() === false) {
                Account::factory()->asOut()
                    ->withResource($achAccount)
                    ->forUser($achAccount->user)
                    ->create();
            }
        });
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->getKey(),
            ];
        });
    }

}
