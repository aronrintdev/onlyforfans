<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

use App\Models\User;
use App\Models\Staff;

/**
 * @group chatmessages
 * @package Tests\Feature
 */
class RestStaffTest extends TestCase
{
    use WithFaker;

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_fetch_manager_list()
    {
        $staff = Staff::where('role', 'manager')->firstOrFail(); // Find the creator account with managers
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $payload = [ 
            'page' => 1,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('staff.indexManagers', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'id',
                    'name',
                    'role',
                    'active',
                    'pending',
                    'email',
                    'last_login_at',
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $managers = collect($content->data);
        foreach($managers as $manager) {
            $dt = Staff::find($manager->id);
            $this->assertNotNull($dt->owner_id);
            $this->assertEquals($dt->owner_id, $sessionUser->id);
        }
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

