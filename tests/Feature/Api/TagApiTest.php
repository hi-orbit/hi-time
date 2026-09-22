<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $apiKey;

    private $customerA;

    private $customerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->apiKey = $this->user->generateApiKey();
        $this->customerA = Customer::create(['name' => 'Customer A']);
        $this->customerB = Customer::create(['name' => 'Customer B']);
    }

    private function headers(?string $key = null): array
    {
        $key = $key ?? $this->apiKey;

        return [
            'X-API-Key' => $key,
            'Accept' => 'application/json',
        ];
    }

    private function makeCustomerUser(): User
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $this->customerA->id]);
        $customer->assignedProjects()->attach($project->id);

        return $customer;
    }

    /** @test */
    public function it_returns_401_when_no_api_key_is_provided()
    {
        $this->getJson('/api/tags', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    /** @test */
    public function it_lists_tags_with_related_data()
    {
        Tag::factory()->create(['name' => 'frontend', 'customer_id' => $this->customerA->id]);
        Tag::factory()->create(['name' => 'backend', 'customer_id' => $this->customerB->id]);

        $response = $this->getJson('/api/tags', $this->headers());

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'name', 'color', 'description',
                    'customer' => ['id', 'name'],
                    'created_at', 'updated_at',
                ]],
            ]);

        // Ordered by name
        $this->assertSame('backend', $response->json('data.0.name'));
        $this->assertSame('frontend', $response->json('data.1.name'));
    }

    /** @test */
    public function it_filters_tags_by_customer_and_name()
    {
        Tag::factory()->create(['name' => 'frontend', 'customer_id' => $this->customerA->id]);
        Tag::factory()->create(['name' => 'frontend-b', 'customer_id' => $this->customerA->id]);
        Tag::factory()->create(['name' => 'backend', 'customer_id' => $this->customerB->id]);

        $this->getJson('/api/tags?customer_id='.$this->customerA->id, $this->headers())
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/tags?name=frontend', $this->headers())
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/tags?customer_id='.$this->customerA->id.'&name=backend', $this->headers())
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function a_customer_only_sees_tags_for_their_customers()
    {
        Tag::factory()->create(['name' => 'mine', 'customer_id' => $this->customerA->id]);
        Tag::factory()->create(['name' => 'theirs', 'customer_id' => $this->customerB->id]);

        $customer = $this->makeCustomerUser();

        $this->getJson('/api/tags', $this->headers($customer->generateApiKey()))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'mine');
    }

    /** @test */
    public function it_creates_a_tag()
    {
        $response = $this->postJson('/api/tags', [
            'name' => 'api-tag',
            'customer_id' => $this->customerA->id,
        ], $this->headers());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'api-tag')
            ->assertJsonPath('data.color', '#3B82F6')
            ->assertJsonPath('data.customer.id', $this->customerA->id);

        $this->assertDatabaseHas('tags', [
            'name' => 'api-tag',
            'customer_id' => $this->customerA->id,
            'color' => '#3B82F6',
        ]);
    }

    /** @test */
    public function it_creates_a_tag_with_color_and_description()
    {
        $this->postJson('/api/tags', [
            'name' => 'styled',
            'color' => '#EF4444',
            'description' => 'A red tag',
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.color', '#EF4444')
            ->assertJsonPath('data.description', 'A red tag')
            ->assertJsonPath('data.customer', null);
    }

    /** @test */
    public function it_returns_422_when_name_is_missing_or_color_is_invalid()
    {
        $this->postJson('/api/tags', ['color' => '#3B82F6'], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->postJson('/api/tags', ['name' => 'bad', 'color' => 'red'], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['color']);

        $this->postJson('/api/tags', ['name' => 'bad', 'customer_id' => 99999], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function tag_names_are_unique_per_customer_scope()
    {
        Tag::factory()->create(['name' => 'dup', 'customer_id' => $this->customerA->id]);

        // Same name, same customer -> 422
        $this->postJson('/api/tags', ['name' => 'dup', 'customer_id' => $this->customerA->id], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Same name, different customer -> allowed
        $this->postJson('/api/tags', ['name' => 'dup', 'customer_id' => $this->customerB->id], $this->headers())
            ->assertCreated();
    }

    /** @test */
    public function a_customer_cannot_create_tags_for_other_customers()
    {
        $customer = $this->makeCustomerUser();
        $key = $customer->generateApiKey();

        // Foreign customer
        $this->postJson('/api/tags', [
            'name' => 'not-mine',
            'customer_id' => $this->customerB->id,
        ], $this->headers($key))
            ->assertForbidden();

        // Global tag (no customer)
        $this->postJson('/api/tags', ['name' => 'global'], $this->headers($key))
            ->assertForbidden();

        $this->assertDatabaseCount('tags', 0);
    }

    /** @test */
    public function a_customer_can_create_tags_for_their_customer()
    {
        $customer = $this->makeCustomerUser();

        $this->postJson('/api/tags', [
            'name' => 'mine',
            'customer_id' => $this->customerA->id,
        ], $this->headers($customer->generateApiKey()))
            ->assertCreated()
            ->assertJsonPath('data.customer.id', $this->customerA->id);
    }
}
