<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use DB;
use Database\Seeders\TestDatabaseSeeder;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Timeline;
use App\Models\UserSetting;

class UserSettingModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @group settings-model
     * @group here0429
     */
    public function test_can_enable_and_disable_notification_settings()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $userSettings = $user->settings;

        $group = 'notifications';

        $payload = [
            'income' => [
                'new_tip' => [ 'email', 'sms' ],
            ],
        ];
        $result = $userSettings->enable($group, $payload);
        $userSettings->refresh();
        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('global', $userSettings->cattrs['notifications']);
        $this->assertArrayHasKey('income', $userSettings->cattrs['notifications']);
        $this->assertArrayHasKey('new_tip', $userSettings->cattrs['notifications']['income']);
        $this->assertContains('email', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertContains('sms', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertNotContains('site', $userSettings->cattrs['notifications']['income']['new_tip']);

        $payload = [
            'income' => [
                'new_tip' => [ 'site' ],
            ],
        ];
        $result = $userSettings->enable($group, $payload);
        $userSettings->refresh();

        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('income', $userSettings->cattrs['notifications']);
        $this->assertContains('email', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertContains('sms', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertContains('site', $userSettings->cattrs['notifications']['income']['new_tip']);
        //dd($userSettings->cattrs);

        $payload = [
            'income' => [
                'new_tip' => [ 'site', 'sms' ],
            ],
        ];
        $result = $userSettings->disable($group, $payload);
        $userSettings->refresh();

        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('income', $userSettings->cattrs['notifications']);
        $this->assertArrayHasKey('new_tip', $userSettings->cattrs['notifications']['income']);
        $this->assertContains('email', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertNotContains('sms', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertNotContains('site', $userSettings->cattrs['notifications']['income']['new_tip']);

        $payload = [
            'income' => [
                'new_tip' => [ 'email' ],
            ],
        ];
        $result = $userSettings->disable($group, $payload);
        $userSettings->refresh();

        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('income', $userSettings->cattrs['notifications']);
        $this->assertArrayHasKey('new_tip', $userSettings->cattrs['notifications']['income']);
        $this->assertNotContains('site', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertEmpty($userSettings->cattrs['notifications']['income']['new_tip']);
    }

    /**
     * @group settings-model
     * @group OFF-here0429
     */
    public function test_can_init_notification_settings_from_null()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $userSettings = $user->settings;

        // Init to null to test corner case
        $userSettings->cattrs = null;
        $userSettings->save();

        $group = 'notifications';

        $payload = [
            'income' => [
                'new_tip' => [ 'email', 'sms' ],
            ],
        ];
        $result = $userSettings->enable($group, $payload);
        $userSettings->refresh();
        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('global', $userSettings->cattrs['notifications']); // %NOTE: this will fail due to cattrs = null above
        $this->assertArrayHasKey('income', $userSettings->cattrs['notifications']);
        $this->assertArrayHasKey('new_tip', $userSettings->cattrs['notifications']['income']);
        $this->assertContains('email', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertContains('sms', $userSettings->cattrs['notifications']['income']['new_tip']);
        $this->assertNotContains('site', $userSettings->cattrs['notifications']['income']['new_tip']);
    }

    /**
     * @group settings-model
     * @group here0429
     */
    public function test_can_enable_and_disable_global_notification_setting()
    {
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $user = $timeline->user;

        $userSettings = $user->settings;

        // Init to null to test corner case
        $userSettings->cattrs = null;
        $userSettings->save();

        $group = 'notifications';

        $payload = [
            'global' => [ 'email', 'sms' ],
        ];
        $result = $userSettings->enable($group, $payload);
        $userSettings->refresh();
        $this->assertArrayHasKey('notifications', $userSettings->cattrs);
        $this->assertArrayHasKey('global', $userSettings->cattrs['notifications']);
        $this->assertContains('email', $userSettings->cattrs['notifications']['global']);
        $this->assertContains('sms', $userSettings->cattrs['notifications']['global']);
        $this->assertNotContains('site', $userSettings->cattrs['notifications']['global']);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}
