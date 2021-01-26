<?php

namespace Database\Factories;

use App\User;
use App\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $password;

        $isFollowForFree = $this->faker->boolean(70);

        $gender = $this->faker->randomElement(['male','female']);
        $firstName = $this->faker->firstName($gender);
        $lastName = $this->faker->lastName;
        $username = strtolower($firstName.'.'.$lastName);
        $fullName = $firstName.' '.$lastName;

        return [
            'email' => $username.'@example.com', // $this->faker->unique()->safeEmail,
            'password' => $password ?: $password = bcrypt('foo-123'), // secret
            'remember_token' => str_random(10),
            'verification_code' => str_random(10),
            'timeline_id' => function () use($fullName, $username) {
                return Timeline::factory()->create([
                    'name' => $fullName,
                    'username' => $username,
                ])->id;
            },
            'city' => $this->faker->city,
            'country' => $this->faker->country,
            'website' => $this->faker->url,
            'instagram' => $this->faker->url,
            'gender' => $gender, // $this->faker->randomElement(['male','female']),
            'active' => 1,
            'verified' => 1,
            'email_verified' => 1,
            'is_follow_for_free' => $isFollowForFree,
            'price' => $isFollowForFree ? null : $this->faker->randomFloat(2, 1, 300),
            'birthday' => $this->faker->dateTimeBetween('1970-01-01', '1998-12-31')->format('Y-m-d'),
        ];
    }
}
