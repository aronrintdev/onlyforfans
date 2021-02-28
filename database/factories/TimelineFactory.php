<?php

namespace Database\Factories;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimelineFactory extends Factory
{
    protected $model = Timeline::class;

    public function definition()
    {
        $isFollowForFree = $this->faker->boolean(70);

        return [
            'name'     => $this->faker->name,
            'about'    => $this->faker->text,
            'verified' => 1,
            'is_follow_for_free' => $isFollowForFree,
            'price' => $isFollowForFree ? 0.00 : $this->faker->randomFloat(2, 1, 300),
        ];
    }
}
