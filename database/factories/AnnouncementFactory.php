<?php

namespace Database\Factories;

use App\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'       => $this->faker->name,
            'description' => $this->faker->text,
            // 'image'      => $this->faker->randomElement($array = array('1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg', '12.jpg', '13.jpg', '1.png', '2.png', '3.png', '4.png')),
            'start_date'  => $this->faker->dateTime($max = 'now'),
            'end_date'    => $this->faker->dateTime($max = 'now'),
        ];
    }
}
