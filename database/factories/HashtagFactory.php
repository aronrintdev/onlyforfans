<?php

namespace Database\Factories;

use App\Hashtag;
use Illuminate\Database\Eloquent\Factories\Factory;

class HashtagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hashtag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tag'             => $this->faker->word,
            'last_trend_time' => $this->faker->dateTime($max = 'now'),
            'count'           => $this->faker->numberBetween($min = 3, $max = 60),
        ];
    }
}
