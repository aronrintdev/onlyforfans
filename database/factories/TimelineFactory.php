<?php

namespace Database\Factories;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimelineFactory extends Factory
{
    protected $model = Timeline::class;

    public function definition()
    {
        $isFollowForFree = $this->faker->boolean(70);

        return [
            'user_id'  => User::factory(),
            'name'     => $this->faker->name,
            'about'    => $this->faker->text,
            'verified' => 1,
            'is_follow_for_free' => $isFollowForFree,
            'price' => $isFollowForFree ? 0.00 : $this->faker->numberBetween(300, 4000),
        ];
    }
}
