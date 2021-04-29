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
    public function test_can_init_settings()
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
        $this->assertArrayHasKey('new_tip', $userSettings->cattrs['notifications']['income']);
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

        /*
        $payload = [
            'income' => [
                //'new_tip' => ['email', 'sms'],
                'new_tip' => ['email'],
            ],
        ];
        $response = $this->actingAs($user)->ajaxJSON('PATCH', route('users.enableSetting', [$user->id, 'notifications']), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        dd($content);


        $story = factory(Story::class)->create();
        $story->mediafiles()->save(factory(Mediafile::class)->create([
            'resource_type' => 'stories',
            'mftype' => MediafileTypeEnum::STORY,
        ]));
        $story->refresh();
        $this->assertNotNull($story);
        $this->assertNotNull($story->id);
        $this->assertNotNull($story->mediafiles);
        $this->assertNotNull($story->mediafiles->first());
         */
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
