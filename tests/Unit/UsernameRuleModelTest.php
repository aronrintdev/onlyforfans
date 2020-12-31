<?php

namespace Tests\Unit;

use App\UsernameRule;

use Tests\TestCase;
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
        // Setup
        $username = UsernameRule::seed(0)->create_random();
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

        $username = UsernameRule::seed(0)->create_random();

        $this->assertNotEquals($username, 'u4488659055');
    }
}
