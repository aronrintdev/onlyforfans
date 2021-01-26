<?php

namespace Database\Factories;

use App\Timeline;
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
        return [
            'username' => $this->faker->userName,
            'name'     => $this->faker->name,
            'about'    => $this->faker->text,
            'type'    => 'user',
            // 'avatar_id' => $this->faker->numberBetween($min = 1, $max = 80),
            // 'cover_id' => $this->faker->numberBetween($min = 1, $max = 80),

        ];
    }
}
