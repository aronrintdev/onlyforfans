<?php

namespace Tests\Feature;

use App\Models\Mycontact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @group mycontacts
 *
 * @package Tests\Feature
 */
class RestMycontactsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $owner;
    private $nonOwner;

    public function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->nonOwner = User::factory()->create();
    }

    public function test_can_create()
    {
        $payload = [
            'contact_id' => $this->nonOwner->getKey(),
            'alias' => $this->faker->name,
            'cattrs' => '{ "custom": "testing" }',
            'meta' => '{ "custom": "testing" }',
        ];
        $response = $this->actingAs($this->owner)->ajaxJSON('POST', route('mycontacts.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertNotNull($content->data->id);
        $this->assertNotNull($content->data->contact_id);
        $this->assertNotNull($content->data->alias);
        $this->assertNotNull($content->data->created_at);
        $this->assertNotNull($content->data->updated_at);

        $this->assertDatabaseHas(Mycontact::getTableName(), [
            'owner_id' => $this->owner->getKey(),
            'contact_id' => $this->nonOwner->getKey(),
            'alias' => $payload['alias'],
        ]);
    }

    private function setupNewMycontact()
    {
        $payload = [
            'contact_id' => $this->nonOwner->getKey(),
            'alias' => $this->faker->name,
            'cattrs' => '{ "custom": "testing" }',
            'meta' => '{ "custom": "testing" }',
        ];
        $response = $this->actingAs($this->owner)->ajaxJSON('POST', route('mycontacts.store'), $payload);
        return json_decode($response->content())->data->id;
    }

    /**
     * @depends test_can_create
     */
    public function test_owner_can_update()
    {
        $mycontactId = $this->setupNewMycontact();

        $payload = [
            'alias'  => 'A different name',
            'cattrs' => '{ "custom": "testing" }',
            'meta'   => '{ "custom": "testing" }',
        ];
        $response = $this->actingAs($this->owner)->ajaxJSON('PATCH', route('mycontacts.update', [ 'mycontact' => $mycontactId ]), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertNotNull($mycontactId);
        $this->assertNotNull($content->data->contact_id);
        $this->assertEquals('A different name',$content->data->alias);
        $this->assertNotNull($content->data->created_at);
        $this->assertNotNull($content->data->updated_at);

        $this->assertDatabaseHas(Mycontact::getTableName(), [
            'id' => $mycontactId,
            'alias' => 'A different name',
        ]);
    }

    /**
     * @depends test_owner_can_update
     */
    public function test_non_owner_cannot_update()
    {
        $mycontactId = $this->setupNewMycontact();

        $payload = [
            'alias'  => 'A different name',
            'cattrs' => '{ "custom": "testing" }',
            'meta'   => '{ "custom": "testing" }',
        ];
        $response = $this->actingAs($this->nonOwner)->ajaxJSON('PATCH', route('mycontacts.update', ['mycontact' => $mycontactId]), $payload);
        $response->assertStatus(403);
    }

    /**
     * @depends test_can_create
     */
    public function test_owner_can_delete()
    {
        $mycontactId = $this->setupNewMycontact();

        $response = $this->actingAs($this->owner)->ajaxJSON('DELETE', route('mycontacts.destroy', ['mycontact' => $mycontactId]));
        $response->assertStatus(200);

    }

    /**
     * @depends test_owner_can_delete
     */
    public function test_non_owner_cannot_delete()
    {
        $mycontactId = $this->setupNewMycontact();

        $response = $this->actingAs($this->nonOwner)->ajaxJSON('DELETE', route('mycontacts.destroy', ['mycontact' => $mycontactId]));
        $response->assertStatus(403);
    }

}