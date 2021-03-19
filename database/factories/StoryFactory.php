<?php

namespace Database\Factories;

use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Story::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Creates an associated user/timeline (unless timeline_id is passed in ?)
        $attrs = [
            'content'     => $this->faker->text,
            'stype'        => 'text', // for image, need to override from caller
            'timeline_id' => function () {
                $user = User::factory()->create();
                return $user->timeline->id;
            },
        ];
        return $attrs;
    }
}
