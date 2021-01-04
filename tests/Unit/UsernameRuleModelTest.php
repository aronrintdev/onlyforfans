<?php

namespace Tests\Unit;

use App\UsernameRule;

use Tests\TestCase;
use Faker\Factory as Faker;
// use Tests\MigrateFreshOnce;
// use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;

class UsernameRuleModelTest extends TestCase
{
    // use MigrateFreshOnce;

    /**
     * If config value is present
     *
     * @return void
     */
    public function testConfigPresent() {
        $this->assertNotNull(\config('users.generatedUsernameTemplate'));
    }

    /**
     * Generate random username works correctly with no other users.
     * Note: `u4488659055` is true for seed `0` with pattern `u##########`
     */
    public function testRandomGenerate() {
        $faker = Faker::create();
        $faker->seed(0);
        $username = UsernameRule::createRandom('u##########', $faker);
        $this->assertEquals($username, 'u4488659055');
    }

    /**
     * Random Username does not collide with other username
     */
    public function testRandomCollidePrevention() {
        // Add User to DB with username `u4488659055`
        $timeline = factory(\App\Timeline::class)->create([
            'username' => 'u4488659055'
        ]);
        $user = factory(\App\User::class)->create([
            'timeline' => $timeline->id
        ]);

        $faker = Faker::create();
        $faker->seed(0);
        $username = UsernameRule::createRandom('u##########', $faker);

        $this->assertNotEquals($username, 'u4488659055');
    }
}
