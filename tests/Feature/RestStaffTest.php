<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification as NotificationFacade;

use Tests\TestCase;

use App\Notifications\InviteStaffMember;
use App\Notifications\InviteStaffManager;

use App\Models\User;
use App\Models\Staff;
use App\Models\Timeline;
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
     *  @group here0924
     */
    public function test_can_send_staff_manager_invitation_as_guest()
    {
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        } else {
            //Mail::fake(); // -- not applicable for guest case
        }

        // Find a creator account with managers (?? %FIXME)
        $staffAccountWithManagerRole = Staff::where('role', 'manager')->firstOrFail();
        $sessionUser = User::where('id', $staffAccountWithManagerRole->owner_id)->firstOrFail();

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'role' => 'manager',
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staffaccounts.store', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [ 'id', 'name', 'first_name', 'last_name', 'email', 'role', 'active', 'pending', 'owner_id', 'creator_id', 'user_id', 'settings', 'last_login_at', 'created_at' ]
        ]);

        // Check response
        $this->assertNotNull($content->data->id);
        $this->assertEquals($payload['first_name'], $content->data->first_name);
        $this->assertEquals($payload['last_name'], $content->data->last_name);
        $this->assertEquals($payload['email'], $content->data->email);
        $this->assertEquals('manager', $content->data->role);
        $this->assertTrue($content->data->pending);
        $this->assertFalse($content->data->active);
        $this->assertNull($content->data->creator_id);
        $this->assertNull($content->data->user_id);

        // Check database
        $sa = Staff::find($content->data->id);
        $this->assertNotNull($sa->id??null);
        $this->assertEquals($payload['first_name'], $sa->first_name);
        $this->assertEquals($payload['last_name'], $sa->last_name);
        $this->assertEquals($payload['email'], $sa->email);
        $this->assertEquals('manager', $sa->role);
        $this->assertTrue(!!$sa->pending);
        $this->assertFalse(!!$sa->active);
        $this->assertNull($sa->creator_id);
        $this->assertNull($sa->user_id);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $toStr = $payload['first_name'].' '.$payload['last_name'].' <'.$payload['email'].'>';
                $this->assertStringContainsStringIgnoringCase('To: '.$toStr, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been invited to become a manager', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Manager', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }
    }

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     *  @group here0924
     */
    public function test_can_send_staff_manager_invitation_as_registered_user()
    {
        NotificationFacade::fake();

        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        } else {
            Mail::fake();
        }

        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;

        $existingStaff = Staff::where('role', 'manager')->where('owner_id', $creator->id)->pluck('email')->toArray();
        $notInA = $existingStaff;
        $notInA[] = $creator->email;
        $preManager = User::whereNotIn('email', $notInA)->first();
        $this->assertNotNull($preManager->id??null);
        $this->assertFalse( in_array($preManager->id, $existingStaff) );
        $this->assertFalse( $preManager->id === $creator->id );

        $payload = [
            'first_name' => $preManager->real_firstname ?? $preManager->name,
            'last_name' => $preManager->real_lastname ?? 'Smith',
            'email' => $preManager->email,
            'role' => 'manager',
        ];

        $response = $this->actingAs($creator)->ajaxJSON( 'POST', route('staffaccounts.store', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [ 'id', 'name', 'first_name', 'last_name', 'email', 'role', 'active', 'pending', 'owner_id', 'creator_id', 'user_id', 'settings', 'last_login_at', 'created_at' ]
        ]);

        // Check response
        $this->assertNotNull($content->data->id);
        $this->assertEquals($payload['first_name'], $content->data->first_name);
        $this->assertEquals($payload['last_name'], $content->data->last_name);
        $this->assertEquals($payload['email'], $content->data->email);
        $this->assertEquals('manager', $content->data->role);
        $this->assertTrue($content->data->pending);
        $this->assertFalse($content->data->active);
        $this->assertNull($content->data->creator_id);
        $this->assertNull($content->data->user_id);

        // Check database
        $sa = Staff::find($content->data->id);
        $this->assertNotNull($sa->id??null);
        $this->assertEquals($payload['first_name'], $sa->first_name);
        $this->assertEquals($payload['last_name'], $sa->last_name);
        $this->assertEquals($payload['email'], $sa->email);
        $this->assertEquals('manager', $sa->role);
        $this->assertTrue(!!$sa->pending);
        $this->assertFalse(!!$sa->active);
        $this->assertNotNull($sa->creator_id); // %FIXME
        $this->assertNotNull($sa->user_id); // %FIXME

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                //$toStr = $payload['first_name'].' '.$payload['last_name'].' <'.$payload['email'].'>';
                $toStr = $payload['email'];
                $this->assertStringContainsStringIgnoringCase('To: '.$toStr, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been invited to become a manager', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Manager', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }

        NotificationFacade::assertSentTo( [$preManager], InviteStaffManager::class );
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
     *  @group here0924
     */
    public function test_can_send_staff_member_invitation_as_guest()
    {
        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        } else {
            //Mail::fake(); // -- not applicable for guest case
        }

        // Find a creator account with managers (?? %FIXME)
        $staffAccountWithManagerRole = Staff::where('role', 'manager')->firstOrFail();
        $sessionUser = User::where('id', $staffAccountWithManagerRole->owner_id)->firstOrFail();

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'role' => 'member',
        ];

        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('staffaccounts.store', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [ 'id', 'name', 'first_name', 'last_name', 'email', 'role', 'active', 'pending', 'owner_id', 'creator_id', 'user_id', 'settings', 'last_login_at', 'created_at' ]
        ]);

        // Check response
        $this->assertNotNull($content->data->id);
        $this->assertEquals($payload['first_name'], $content->data->first_name);
        $this->assertEquals($payload['last_name'], $content->data->last_name);
        $this->assertEquals($payload['email'], $content->data->email);
        $this->assertEquals('member', $content->data->role);
        $this->assertTrue($content->data->pending);
        $this->assertFalse($content->data->active);
        $this->assertNull($content->data->creator_id);
        $this->assertNull($content->data->user_id);

        // Check database
        $sa = Staff::find($content->data->id);
        $this->assertNotNull($sa->id??null);
        $this->assertEquals($payload['first_name'], $sa->first_name);
        $this->assertEquals($payload['last_name'], $sa->last_name);
        $this->assertEquals($payload['email'], $sa->email);
        $this->assertEquals('member', $sa->role);
        $this->assertTrue(!!$sa->pending);
        $this->assertFalse(!!$sa->active);
        $this->assertNull($sa->creator_id);
        $this->assertNull($sa->user_id);

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                $toStr = $payload['first_name'].' '.$payload['last_name'].' <'.$payload['email'].'>';
                $this->assertStringContainsStringIgnoringCase('To: '.$toStr, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been added as a staff member', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Member', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }
    }

    /**
     *  @group regression
     *  @group regression-base
     *  @group staff
     *  @group here0924
     */
    public function test_can_send_staff_member_invitation_as_registered_user()
    {
        NotificationFacade::fake();

        $lPath = self::getLogPath();
        $isLogScanEnabled = Config::get('sendgrid.testing.scan_log_file_to_check_emails', false);
        if( $isLogScanEnabled ) {
            $fSizeBefore = $lPath ? filesize($lPath) : null;
        } else {
            Mail::fake();
        }

        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;

        $existingStaff = Staff::where('role', 'member')->where('owner_id', $creator->id)->pluck('email')->toArray();
        $notInA = $existingStaff;
        $notInA[] = $creator->email;
        $preMember = User::whereNotIn('email', $notInA)->first();
        $this->assertNotNull($preMember->id??null);
        $this->assertFalse( in_array($preMember->id, $existingStaff) );
        $this->assertFalse( $preMember->id === $creator->id );

        $payload = [
            'first_name' => $preMember->real_firstname ?? $preMember->name,
            'last_name' => $preMember->real_lastname ?? 'Smith',
            'email' => $preMember->email,
            'role' => 'member',
        ];

        $response = $this->actingAs($creator)->ajaxJSON( 'POST', route('staffaccounts.store', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [ 'id', 'name', 'first_name', 'last_name', 'email', 'role', 'active', 'pending', 'owner_id', 'creator_id', 'user_id', 'settings', 'last_login_at', 'created_at' ]
        ]);

        // Check response
        $this->assertNotNull($content->data->id);
        $this->assertEquals($payload['first_name'], $content->data->first_name);
        $this->assertEquals($payload['last_name'], $content->data->last_name);
        $this->assertEquals($payload['email'], $content->data->email);
        $this->assertEquals('member', $content->data->role);
        $this->assertTrue($content->data->pending);
        $this->assertFalse($content->data->active);
        $this->assertNull($content->data->creator_id);
        $this->assertNull($content->data->user_id);

        // Check database
        $sa = Staff::find($content->data->id);
        $this->assertNotNull($sa->id??null);
        $this->assertEquals($payload['first_name'], $sa->first_name);
        $this->assertEquals($payload['last_name'], $sa->last_name);
        $this->assertEquals($payload['email'], $sa->email);
        $this->assertEquals('member', $sa->role);
        $this->assertTrue(!!$sa->pending);
        $this->assertFalse(!!$sa->active);
        $this->assertNotNull($sa->creator_id); // %FIXME
        $this->assertNotNull($sa->user_id); // %FIXME

        if ( $isLogScanEnabled && $lPath ) {
            $fSizeAfter = filesize($lPath);
            if ( $fSizeBefore && $fSizeAfter && ($fSizeAfter > $fSizeBefore) ) {
                $fDiff = $fSizeAfter > $fSizeBefore;
                $fcontents = file_get_contents($lPath, false, null, -($fDiff-2));
                //$toStr = $payload['first_name'].' '.$payload['last_name'].' <'.$payload['email'].'>';
                $toStr = $payload['email'];
                $this->assertStringContainsStringIgnoringCase('To: '.$toStr, $fcontents);
                $this->assertStringContainsStringIgnoringCase('been added as a staff member', $fcontents);
                $this->assertStringContainsString('Subject: Invite Staff Member', $fcontents);
                $this->assertStringContainsString('From:', $fcontents);
            }
        }

        NotificationFacade::assertSentTo( [$preMember], InviteStaffMember::class );
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

