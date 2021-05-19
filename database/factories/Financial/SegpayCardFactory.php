<?php

namespace Database\Factories\Financial;

use App\Models\Financial\Account;
use App\Models\Financial\SegpayCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SegpayCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * @var string
     */
    protected $model = SegpayCard::class;


    /**
     * Define the model's default state
     *
     * @return array
     */
    public function definition()
    {
        return [
            'owner_type' => app(User::class)->getMorphString(),
            'owner_id' => User::factory(),
            'token' => 'generated',
            'nickname' => 'Generated Credit Card',
            'card_type' => $this->faker->randomElement([ 'visa', 'mastercard', 'jcb', 'discover', ]),
            'last_4' => $this->faker->randomNumber(4, true),
        ];
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'owner_type' => $user->getMorphString(),
                'owner_id' => $user->getKey(),
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (SegpayCard $card) {
            Account::factory()->asIn()->withCard($card)->create([
                'owner_id' => $card->getOwner()->first()->getKey(),
                'owner_type' => $card->getOwner()->first()->getMorphString(),
            ]);
        });
    }

}
