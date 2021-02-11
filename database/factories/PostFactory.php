<?php

namespace Database\Factories;

use App\Models\Post;
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
        $type = $this->faker->randomElement([
            PostTypeEnum::SUBSCRIBER,
            PostTypeEnum::PRICED,
            PostTypeEnum::FREE,
            PostTypeEnum::FREE,
            PostTypeEnum::FREE,
        ]);
        $attrs = [
            'description'  => $this->faker->text . ' (' . $type . ')',
            'type'         => $type,
            'active'       => 1,
        ];
        if ( $type === PostTypeEnum::PRICED ) {
            $attrs['price'] = $this->faker->randomFloat(2, 1, 300);
            $attrs['currency'] = 'USD';
        }
        return $attrs;
    }
}
