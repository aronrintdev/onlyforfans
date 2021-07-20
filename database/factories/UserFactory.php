<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Timeline;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        static $password;

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $username = strtolower($firstName.'.'.$lastName);
        $fullName = $firstName . ' ' . $lastName;

        return [
            'username' => $username,
            'email' => $username . '@example.com', // $this->faker->unique()->safeEmail,
            'password' => $password ?: $password = bcrypt('foo-123'), // secret
            'real_firstname' => $firstName,
            'real_lastname' => $lastName,
            //'remember_token' => str_random(10),
            //'verification_code' => str_random(10),
            'email_verified' => 1,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            Timeline::factory()->create([
                'user_id' => $user->id,
                'name' => ucwords(Str::of($user->username)->replaceFirst('.', ' ')),
            ]);
            /* %TODO: see https://trello.com/c/LzTUmPCp
            FinancialAccount::factory()->create([
                'user_id' => $user->id,
                'name' => ucwords(Str::of($user->username)->replaceFirst('.', ' ')),
            ]);
             */
        });
    }

}
