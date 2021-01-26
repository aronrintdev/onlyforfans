<?php

namespace Database\Factories;

use App\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Album::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'timeline_id'   => $this->faker->numberBetween($min = 1, $max = 90),
            'preview_id'    => $this->faker->numberBetween($min = 1, $max = 80),
            'name'          => $this->faker->streetName,
            'about'         => $this->faker->text($maxNbChars = 80),
            'active'        => 1,
            'privacy'       => $this->faker->randomElement(['private', 'public']),
        ];
    }
}
