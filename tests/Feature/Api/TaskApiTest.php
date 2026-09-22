<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $apiKey;

    private $project;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->apiKey = $this->user->generateApiKey();
        $this->project = Project::factory()->create();
        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
        ]);
    }

    private function headers(?string $key = null): array
    {
        $key = $key ?? $this->apiKey;

        return [
            'X-API-Key' => $key,
            'Accept' => 'application/json',
        ];
    }

    /** @test */
    public function it_returns_401_when_no_api_key_is_provided()
    {
        $this->getJson('/api/tasks', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    /** @test */
    public function it_returns_401_for_an_invalid_api_key()
    {
        $this->getJson('/api/tasks', $this->headers('invalid-key'))
            ->assertStatus(401)
            ->assertJsonFragment(['message' => 'Invalid or missing API key.']);
    }

    /** @test */
    public function it_authenticates_via_bearer_token()
    {
        $this->getJson('/api/tasks', [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/json',
        ])->assertOk();
    }

    /** @test */
    public function it_lists_tasks_with_related_data()
    {
        $assignee = User::factory()->create();
        $tag = Tag::factory()->create();
        $this->task->update(['assigned_to' => $assignee->id]);
        $this->task->tags()->attach($tag->id);

        $response = $this->getJson('/api/tasks', $this->headers());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'description', 'status', 'order',
                    'project' => ['id', 'name'],
                    'assigned_user' => ['id', 'name'],
                    'created_by' => ['id', 'name'],
                    'tags', 'total_time_minutes', 'created_at', 'updated_at',
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $payload = $response->json('data.0');
        $this->assertSame($this->task->id, $payload['id']);
        $this->assertSame($this->project->name, $payload['project']['name']);
        $this->assertSame($assignee->name, $payload['assigned_user']['name']);
        $this->assertSame($tag->name, $payload['tags'][0]['name']);
    }

    /** @test */
    public function it_filters_tasks_by_project_status_and_assignee()
    {
        $otherProject = Project::factory()->create();
        $assignee = User::factory()->create();

        Task::factory()->create([
            'project_id' => $otherProject->id,
            'status' => 'done',
            'assigned_to' => $assignee->id,
        ]);

        $this->getJson('/api/tasks?project_id='.$otherProject->id, $this->headers())
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', Task::where('project_id', $otherProject->id)->first()->id);

        $this->getJson('/api/tasks?status=done', $this->headers())
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/tasks?assigned_to='.$assignee->id, $this->headers())
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        // No matches
        $this->getJson('/api/tasks?status=backlog&project_id='.$otherProject->id, $this->headers())
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    /** @test */
    public function it_paginates_tasks()
    {
        // 25 new tasks + the 1 created in setUp
        Task::factory()->count(25)->create(['project_id' => $this->project->id]);

        $this->getJson('/api/tasks?per_page=10', $this->headers())
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 26)
            ->assertJsonPath('meta.last_page', 3);
    }

    /** @test */
    public function it_returns_422_for_invalid_filter_values()
    {
        $this->getJson('/api/tasks?status=not_a_status', $this->headers())
            ->assertStatus(422);

        $this->getJson('/api/tasks?project_id=99999', $this->headers())
            ->assertStatus(422);
    }

    /** @test */
    public function a_customer_only_sees_tasks_for_their_assigned_projects()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customerKey = $customer->generateApiKey();

        $foreignProject = Project::factory()->create();
        Task::factory()->create(['project_id' => $foreignProject->id]);

        $customer->assignedProjects()->attach($this->project->id);

        $this->getJson('/api/tasks', $this->headers($customerKey))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->task->id);
    }

    /** @test */
    public function a_non_customer_sees_all_tasks()
    {
        $staff = User::factory()->create(['role' => 'user']);
        $staffKey = $staff->generateApiKey();

        $foreignProject = Project::factory()->create();
        Task::factory()->create(['project_id' => $foreignProject->id]);

        $this->getJson('/api/tasks', $this->headers($staffKey))
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    /** @test */
    public function it_shows_a_single_task()
    {
        $this->getJson("/api/tasks/{$this->task->id}", $this->headers())
            ->assertOk()
            ->assertJsonPath('data.id', $this->task->id)
            ->assertJsonPath('data.title', $this->task->title)
            ->assertJsonPath('data.project.name', $this->project->name);
    }

    /** @test */
    public function it_returns_404_for_a_missing_task()
    {
        $this->getJson('/api/tasks/99999', $this->headers())
            ->assertNotFound();
    }

    /** @test */
    public function it_creates_a_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'API task',
            'description' => 'Created via API',
            'status' => 'in_progress',
            'project_id' => $this->project->id,
        ], $this->headers());

        $response->assertCreated()
            ->assertJsonPath('data.title', 'API task')
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $response->json('data.id'),
            'created_by' => $this->user->id,
            'project_id' => $this->project->id,
        ]);
    }

    /** @test */
    public function it_creates_a_task_with_tags_and_assignment_notification()
    {
        $assignee = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson('/api/tasks', [
            'title' => 'Tagged task',
            'status' => 'backlog',
            'project_id' => $this->project->id,
            'assigned_to' => $assignee->id,
            'tags' => [$tag->id],
        ], $this->headers());

        $response->assertCreated()
            ->assertJsonCount(1, 'data.tags');

        $taskId = $response->json('data.id');
        $this->assertDatabaseHas('tag_task', ['task_id' => $taskId, 'tag_id' => $tag->id]);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', ['user_id' => $assignee->id]);
    }

    /** @test */
    public function it_returns_422_when_required_fields_are_missing()
    {
        $this->postJson('/api/tasks', ['status' => 'backlog', 'project_id' => $this->project->id], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_returns_422_for_an_invalid_status_or_project()
    {
        $this->postJson('/api/tasks', [
            'title' => 'Bad status',
            'status' => 'exploded',
            'project_id' => $this->project->id,
        ], $this->headers())->assertStatus(422)->assertJsonValidationErrors(['status']);

        $this->postJson('/api/tasks', [
            'title' => 'Bad project',
            'status' => 'backlog',
            'project_id' => 99999,
        ], $this->headers())->assertStatus(422)->assertJsonValidationErrors(['project_id']);
    }

    /** @test */
    public function a_customer_cannot_create_tasks_on_unassigned_projects()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customerKey = $customer->generateApiKey();

        $this->postJson('/api/tasks', [
            'title' => 'Denied task',
            'status' => 'backlog',
            'project_id' => $this->project->id,
        ], $this->headers($customerKey))
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 1);
    }

    /** @test */
    public function a_customer_can_create_tasks_on_assigned_projects()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customerKey = $customer->generateApiKey();
        $customer->assignedProjects()->attach($this->project->id);

        $this->postJson('/api/tasks', [
            'title' => 'Allowed task',
            'status' => 'backlog',
            'project_id' => $this->project->id,
        ], $this->headers($customerKey))
            ->assertCreated();
    }

    /** @test */
    public function it_updates_a_task()
    {
        $this->putJson("/api/tasks/{$this->task->id}", [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => 'in_test',
        ], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.status', 'in_test');

        $this->task->refresh();
        $this->assertSame('Updated description', $this->task->description);
    }

    /** @test */
    public function it_supports_partial_updates()
    {
        $originalTitle = $this->task->title;

        $this->patchJson("/api/tasks/{$this->task->id}", [
            'status' => 'done',
        ], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.title', $originalTitle);
    }

    /** @test */
    public function it_sends_assignment_and_status_notifications_on_update()
    {
        $assignee = User::factory()->create();

        $this->putJson("/api/tasks/{$this->task->id}", [
            'status' => 'in_progress',
            'assigned_to' => $assignee->id,
        ], $this->headers())
            ->assertOk();

        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseHas('notifications', ['user_id' => $assignee->id]);
    }

    /** @test */
    public function it_moves_a_task_to_another_project()
    {
        $target = Project::factory()->create();

        $this->putJson("/api/tasks/{$this->task->id}", [
            'project_id' => $target->id,
        ], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.project.id', $target->id);

        $this->task->refresh();
        $this->assertSame($target->id, $this->task->project_id);
    }

    /** @test */
    public function a_customer_cannot_update_or_move_a_task_outside_their_projects()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customerKey = $customer->generateApiKey();

        $customer->assignedProjects()->attach($this->project->id);

        // Foreign task (customer not assigned to that project)
        $foreignProject = Project::factory()->create();
        $foreignTask = Task::factory()->create(['project_id' => $foreignProject->id]);

        $this->putJson("/api/tasks/{$foreignTask->id}", ['title' => 'Nope'], $this->headers($customerKey))
            ->assertForbidden();

        // Moving own task to an unassigned project
        $this->putJson("/api/tasks/{$this->task->id}", ['project_id' => $foreignProject->id], $this->headers($customerKey))
            ->assertForbidden();

        // Showing/deleting a foreign task
        $this->getJson("/api/tasks/{$foreignTask->id}", $this->headers($customerKey))->assertForbidden();
        $this->deleteJson("/api/tasks/{$foreignTask->id}", [], $this->headers($customerKey))->assertForbidden();
    }

    /** @test */
    public function it_returns_422_for_invalid_update_values()
    {
        $this->putJson("/api/tasks/{$this->task->id}", ['status' => 'exploded'], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_deletes_a_task()
    {
        $this->deleteJson("/api/tasks/{$this->task->id}", [], $this->headers())
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $this->task->id]);

        // Second delete is a 404
        $this->deleteJson("/api/tasks/{$this->task->id}", [], $this->headers())
            ->assertNotFound();
    }

    /** @test */
    public function only_admin_assignee_or_creator_can_delete_a_task()
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $outsider = User::factory()->create(['role' => 'user']);

        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        // Outsider (neither admin, assignee, nor creator) is blocked
        $this->deleteJson("/api/tasks/{$task->id}", [], $this->headers($outsider->generateApiKey()))
            ->assertForbidden();

        // Assignee can delete
        $this->deleteJson("/api/tasks/{$task->id}", [], $this->headers($assignee->generateApiKey()))
            ->assertNoContent();

        // Creator can delete
        $task2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);
        $this->deleteJson("/api/tasks/{$task2->id}", [], $this->headers($creator->generateApiKey()))
            ->assertNoContent();
    }

    /** @test */
    public function it_does_not_expose_the_api_key_in_serialized_users()
    {
        $this->user->refresh();
        $this->assertNotNull($this->user->api_key);
        $this->assertArrayNotHasKey('api_key', $this->user->toArray());
    }
}
