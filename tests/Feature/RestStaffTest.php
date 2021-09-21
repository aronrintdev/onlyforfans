<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationFacade;

use Tests\TestCase;

use App\Models\User;
use App\Models\Staff;
use App\Models\Permission;
use App\Notifications\StaffSettingsChanged;

/**
 * @group staff
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
        // Find a creator account with managers
        $manager = Staff::where('role', 'manager')->firstOrFail();
        $sessionUser = User::where('id', $manager->owner_id)->firstOrFail();

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
                    'settings',
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

    
    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     *  @group here0920
     */
    public function test_can_send_manager_invitation()
    {
        //Mail::fake();

        // Find a creator account with managers
        $manager = Staff::where('role', 'manager')->firstOrFail();
        $sessionUser = User::where('id', $manager->owner_id)->firstOrFail();

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'pending' => true,
            'role' => 'manager',
            'creator_id' => null,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('users.sendStaffInvite', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            'status',
        ]);

        $this->assertEquals(200, $content->status);
    }

      
    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_accept_manager_invitation()
    {
        $pendingManager = Staff::where('role', 'manager')->where('pending', true)->firstOrFail(); // Find a pending manager
        $sessionUser = User::where('email', $pendingManager->email)->firstOrFail();

        // Try with invalid token:
        $payload = [
            'email' => $pendingManager->email,
            'token' => str_random(50),
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals('Invalid email or token', $content->error);

        // Try with invalid email:
        $payload = [
            'email' => $this->faker->email,
            'token' => $pendingManager->token,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals('Invalid email or token', $content->error);

        // Try with correct email & token: 
        $payload = [
            'email' => $pendingManager->email,
            'token' => $pendingManager->token,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(200, $content->status);
    }

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_change_staff_manager_account_status()
    {
        $staff = Staff::where('role', 'manager')->where('active', true)->firstOrFail(); // Find an active manager
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'PATCH', route('staff.changestatus', $staff->id) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'active',
                'role',
                'pending',
                'owner_id',
                'user_id',
                'settings',
            ],
        ]);

        $data = $content->data;
        $this->assertEquals(false, $data->active);
        $this->assertEquals('manager', $data->role);
        $this->assertEquals($sessionUser->id, $data->owner_id);
        $this->assertEquals($staff->email, $data->email);
    }


    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_fetch_staff_member_list()
    {
        // Find a manager with some staff
        $staff = Staff::where('role', 'staff')->firstOrFail();
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('staff.indexStaffMembers') );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            0 => [
                'id',
                'first_name',
                'last_name',
                'email',
                'role',
                'active',
                'pending',
                'owner_id',
                'creator_id',
                'user_id',
                'owner',
                'members' => [
                    0 => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'role',
                        'active',
                        'pending',
                        'owner_id',
                        'creator_id',
                        'user_id',
                        'permissions',
                        'user',
                        'settings',
                    ]
                ]
            ],
        ]);

        foreach($content as $team) {
            $data = collect($team->members);
            foreach($data as $member) {
                $member = Staff::find($staff->id);
                $this->assertNotNull($member->owner_id);
                $this->assertEquals($member->owner_id, $sessionUser->id);
                $this->assertEquals('staff', $member->role);
            }
        }
    }

      
    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_send_staff_member_invitation()
    {
        Mail::fake();

        // Find a staff manager
        $manager = Staff::where('role', 'manager')->where('active', true)->firstOrFail();
        $sessionUser = User::where('id', $manager->user_id)->firstOrFail();

        $permissions = Permission::where('guard_name', 'staff')->pluck('id')->toArray();

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'pending' => true,
            'role' => 'staff',
            'permissions' => array_slice($permissions, 0, 2),
            'creator_id' => null,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('users.sendStaffInvite', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            'status',
        ]);

        $this->assertEquals(200, $content->status);
    }


    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_list_permissions()
    {
        // Find a staff-manager
        $staff = Staff::where('role', 'manager')->where('active', true)->firstOrFail();
        $sessionUser = User::where('id', $staff->user_id)->firstOrFail();

        $permissions = Permission::where('guard_name', 'staff')->get()->toArray();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('staff.permissions') );

        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertEquals(count($permissions), count($content));
    }


    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_accept_staff_member_invitation()
    {
        // Find a pending staff-member
        $pendingStaff = Staff::where('role', 'staff')->where('pending', true)->firstOrFail();
        $sessionUser = User::where('email', $pendingStaff->email)->firstOrFail();

        // Try with invalid token:
        $payload = [
            'email' => $pendingStaff->email,
            'token' => str_random(50),
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals('Invalid email or token', $content->error);

        // Try with invalid email:
        $payload = [
            'email' => $this->faker->email,
            'token' => $pendingStaff->token,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals('Invalid email or token', $content->error);

        // Try with correct email & token: 
        $payload = [
            'email' => $pendingStaff->email,
            'token' => $pendingStaff->token,
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staff.acceptInvite', $payload) );
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(200, $content->status);
    }


    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_change_staff_member_account_status()
    {
        
        $staff = Staff::where('role', 'staff')->where('active', true)->firstOrFail(); // Find an active staff-member
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'PATCH', route('staff.changestatus', $staff->id) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'active',
                'role',
                'pending',
                'owner_id',
                'user_id',
                'settings',
            ],
        ]);

        $data = $content->data;
        $this->assertEquals(false, $data->active);
        $this->assertEquals('staff', $data->role);
        $this->assertEquals($sessionUser->id, $data->owner_id);
        $this->assertEquals($staff->email, $data->email);
    }

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_set_percentage_of_earnings()
    {
        NotificationFacade::fake();
        // Find the creator account with managers
        $manager = Staff::where('role', 'manager')->where('active', true)->firstOrFail();
        $sessionUser = User::where('id', $manager->owner_id)->firstOrFail();

        $payload = [
            'settings' => [
                'earnings' => 40,
            ]
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'PATCH', route('staff.updateManagerSettings', $manager->id), $payload );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'earnings' => [
                'value',
            ]
        ]);
        $content = json_decode($response->content());
        $this->assertEquals('40', $content->earnings->value);
    }

        
    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_remove_staff_member()
    {
        // Find a manager account with staff-members
        $staff = Staff::where('role', 'staff')->firstOrFail();
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'DELETE', route('staff.remove', $staff->id) );

        $response->assertStatus(200);
    }

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     */
    public function test_can_remove_staff_manager()
    {
        // Find the creator account with managers
        $staff = Staff::where('role', 'manager')->where('active', true)->firstOrFail();
        $sessionUser = User::where('id', $staff->owner_id)->firstOrFail();

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'DELETE', route('staff.remove', $staff->id) );

        $response->assertStatus(200);
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

