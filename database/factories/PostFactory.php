<?php

namespace Database\Factories;

use App\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\PostTypeEnum;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ptype = $this->faker->randomElement([
            PostTypeEnum::SUBSCRIBER,
            PostTypeEnum::PRICED,
            PostTypeEnum::FREE,
            PostTypeEnum::FREE,
            PostTypeEnum::FREE,
        ]);
        $attrs = [
            'description'  => $this->faker->text . ' (' . $ptype . ')',
            //'user_id'      => $u->id,
            //'timeline_id'  => $u->timeline->id,
            'type'         => $ptype,
            'active'       => 1,
            'location'     => $this->faker->country,
        ];
        if ( $ptype === PostTypeEnum::PRICED ) {
            $attrs['price'] = $this->faker->randomFloat(2, 1, 300);
        }
        return $attrs;
    }
}
