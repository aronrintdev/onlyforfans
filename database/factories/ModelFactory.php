<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Timeline::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'name'     => $faker->name,
        'about'    => $faker->text,
        // 'avatar_id' => $faker->numberBetween($min = 1, $max = 80),
        // 'cover_id' => $faker->numberBetween($min = 1, $max = 80),

    ];
});

$factory->define(App\Hashtag::class, function (Faker\Generator $faker) {
    return [
        'tag'             => $faker->word,
        'last_trend_time' => $faker->dateTime($max = 'now'),
        'count'           => $faker->numberBetween($min = 3, $max = 60),
    ];
});

$factory->define(App\Category::class, function (Faker\Generator $faker) {
    return [
        'name'        => $faker->name,
        'description' => $faker->text,
        'parent_id'   => $faker->numberBetween($min = 1, $max = 5),
        'active'      => $faker->boolean,
    ];
});

$factory->define(App\Announcement::class, function (Faker\Generator $faker) {
    return [
        'title'       => $faker->name,
        'description' => $faker->text,
        // 'image' => $faker->randomElement($array = array('1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg', '12.jpg', '13.jpg', '1.png', '2.png', '3.png', '4.png')),
        'start_date' => $faker->dateTime($max = 'now'),
        'end_date'   => $faker->dateTime($max = 'now'),
    ];
});

$factory->define(App\Media::class, function (Faker\Generator $faker) {
    return [
        'title'  => $faker->name,
        'type'   => 'image',
        'source' => $faker->randomElement($array = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg', '12.jpg', '13.jpg', '1.png', '2.png', '3.png', '4.png']),
    ];
});

$factory->define(App\Post::class, function (Faker\Generator $faker) {
    return [
        'description' => $faker->text,
        'timeline_id' => $faker->numberBetween($min = 1, $max = 90),
        'user_id'     => $faker->numberBetween($min = 1, $max = 38),
        'active'      => 1,
        'location'    => $faker->country,
        'type'        => $faker->randomElement($array = ['text', 'photo', 'music', 'video', 'location']),

    ];
});

$factory->define(App\Notification::class, function (Faker\Generator $faker) {
    return [
        'post_id'     => $faker->numberBetween($min = 1, $max = 60),
        'user_id'     => $faker->numberBetween($min = 1, $max = 38),
        'notified_by' => $faker->numberBetween($min = 1, $max = 38),
        'seen'        => $faker->boolean,
        'description' => $faker->text,
        'type'        => $faker->randomElement($array = ['follower', 'message', 'following', 'referral', 'post', 'comment', 'like', 'share', 'report']),

    ];
});


$factory->define(App\Conversation::class, function (Faker\Generator $faker) {
    return [
        'creator_id' => 2,
        'type'       => $faker->randomElement($array = ['private', 'group']),
        ];
});

$factory->define(App\Message::class, function (Faker\Generator $faker) {
    return [
        'sender_id'       => $faker->numberBetween($min = 1, $max = 38),
        'description'     => $faker->text,
        'conversation_id' => $faker->numberBetween($min = 1, $max = 40),
    ];
});

$factory->define(App\Album::class, function (Faker\Generator $faker) {
    return [
        'timeline_id' => $faker->numberBetween($min = 1, $max = 90),
        'preview_id'  => $faker->numberBetween($min = 1, $max = 80),
        'name'          => $faker->streetName,
        'about'         => $faker->text($maxNbChars = 80),
        'active'        => 1,
        'privacy'       => $faker->randomElement(['private', 'public']),
    ];
});

$factory->define(App\Story::class, function (Faker\Generator $faker) {
    return [
        'content'     => $faker->text,
        'stype'       => $faker->randomElement(['text']),
        'timeline_id' => function () {
            $user = factory(App\User::class)->create();
            return $user->timeline->id;
        },
    ];
});

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'timeline_id' => function () {
            return factory(App\Timeline::class)->create()->id;
        },
        'city' => $faker->city,
        'country' => $faker->country,
        'website' => $faker->url,
        'instagram' => $faker->url,
        'gender' => $faker->randomElement(['male','female']),
        'active' => 1,
        //'city' => $faker->city,
    ];
});
