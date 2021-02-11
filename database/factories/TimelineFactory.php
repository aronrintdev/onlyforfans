<?php

namespace Database\Factories;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimelineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Timeline::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $isFollowForFree = $this->faker->boolean(70);

        return [
            'name'     => $this->faker->name,
            'about'    => $this->faker->text,
            'verified' => 1,
            'is_follow_for_free' => $isFollowForFree,
            'price' => $isFollowForFree ? null : $this->faker->randomFloat(2, 1, 300),
        ];
    }
}
