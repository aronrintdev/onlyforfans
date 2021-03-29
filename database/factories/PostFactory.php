<?php

namespace Database\Factories;

use Closure;
use App\Models\Post;
use App\Models\Timeline;
use App\Enums\PostTypeEnum;
use App\Interfaces\HasPricePoints;
use App\Models\PurchasablePricePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function configure()
    {
        return $this->afterMaking(function (Post $post) {
            // Fill in missing attributes
            if (!isset($post->postable_id) && !isset($post->user_id)) {
                $timeline = Timeline::factory()->create();
                $post->postable_id = $timeline->getKey();
                $post->postable_type = $timeline->getMorphString();
                $post->user_id = $timeline->user_id;
            } else if (isset($post->postable_id) && !isset($post->user_id)) {
                $post->user_id = $post->timeline->user_id;
            } else if (isset($post->user_id) && !isset($post->postable_id)) {
                $post->postable_id = $post->user->timeline->getKey();
                $post->postable_type = $post->user->timeline->getMorphString();
            }
        })->afterCreating(function (Post $post) {
            if ($post->price->isPositive() && $post instanceof HasPricePoints) {
                PurchasablePricePoint::getDefaultFor($post, $post->price)->saveAsCurrentDefault();
            }
        });
    }

    public function pricedAt($price)
    {
        return $this->state(function (array $attributes) use ($price) {
            if ($price instanceof Closure) {
                $amount = $price();
            } else {
                $amount = $price;
            }
            return [
                'type' => PostTypeEnum::PRICED,
                'description' => $this->faker->text . ' (' . PostTypeEnum::PRICED . ')',
                'price' => $amount,
                'currency' => 'USD',
            ];
        });
    }
}
