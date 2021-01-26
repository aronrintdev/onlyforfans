<?php

namespace Database\Factories;

use App\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'post_id'     => $this->faker->numberBetween($min = 1, $max = 60),
            'user_id'     => $this->faker->numberBetween($min = 1, $max = 38),
            'notified_by' => $this->faker->numberBetween($min = 1, $max = 38),
            'seen'        => $this->faker->boolean,
            'description' => $this->faker->text,
            'type'        => $this->faker->randomElement($array = ['follower', 'message', 'following', 'referral', 'post', 'comment', 'like', 'share', 'report']),
        ];
    }
}
