<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCommentApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $apiKey;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->apiKey = $this->user->generateApiKey();
        $this->task = Task::factory()->create(['created_by' => $this->user->id]);
    }

    private function headers(?string $key = null): array
    {
        $key = $key ?? $this->apiKey;

        return [
            'X-API-Key' => $key,
            'Accept' => 'application/json',
        ];
    }

    private function makeCustomerUser(Task $task): User
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignedProjects()->attach($task->project_id);

        return $customer;
    }

    /** @test */
    public function it_returns_401_when_no_api_key_is_provided()
    {
        $this->getJson("/api/tasks/{$this->task->id}/comments", ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_for_a_nonexistent_task()
    {
        $this->getJson('/api/tasks/99999/comments', $this->headers())
            ->assertNotFound();
    }

    /** @test */
    public function it_lists_comments_newest_first_with_related_data()
    {
        $other = User::factory()->create();

        TaskNote::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $other->id,
            'content' => 'Older comment',
            'created_at' => now()->subDay(),
        ]);

        TaskNote::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Newer comment',
        ]);

        $response = $this->getJson("/api/tasks/{$this->task->id}/comments", $this->headers());

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'task_id', 'content',
                    'user' => ['id', 'name'],
                    'hours', 'minutes', 'total_minutes',
                    'start_time', 'end_time', 'entry_date', 'created_at',
                ]],
            ])
            ->assertJsonPath('data.0.content', 'Newer comment')
            ->assertJsonPath('data.1.content', 'Older comment');

        $this->assertSame($this->user->id, $response->json('data.0.user.id'));
    }

    /** @test */
    public function a_customer_cannot_read_comments_on_a_foreign_task()
    {
        $foreignTask = Task::factory()->create();
        $customer = $this->makeCustomerUser($this->task);

        $this->getJson("/api/tasks/{$foreignTask->id}/comments", $this->headers($customer->generateApiKey()))
            ->assertForbidden();
    }

    /** @test */
    public function it_creates_a_comment()
    {
        $response = $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'A note via the API',
        ], $this->headers());

        $response->assertCreated()
            ->assertJsonPath('data.task_id', $this->task->id)
            ->assertJsonPath('data.content', 'A note via the API')
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.hours', null)
            ->assertJsonPath('data.total_minutes', null);

        $this->assertDatabaseHas('task_notes', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'A note via the API',
            'source' => 'api',
        ]);
    }

    /** @test */
    public function it_creates_a_comment_with_manual_hours_and_minutes()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Manual time',
            'hours' => 2,
            'minutes' => 30,
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.hours', 2)
            ->assertJsonPath('data.minutes', 30)
            ->assertJsonPath('data.total_minutes', 150);
    }

    /** @test */
    public function it_creates_a_comment_with_start_and_end_times()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Timed work',
            'start_time' => '2026-09-21 09:00:00',
            'end_time' => '2026-09-21 10:30:00',
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.start_time', '2026-09-21T09:00:00+00:00')
            ->assertJsonPath('data.end_time', '2026-09-21T10:30:00+00:00')
            ->assertJsonPath('data.entry_date', '2026-09-21')
            ->assertJsonPath('data.total_minutes', 90);
    }

    /** @test */
    public function it_handles_overnight_comments()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Late night work',
            'start_time' => '2026-09-21 23:00:00',
            'end_time' => '2026-09-22 01:00:00',
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.entry_date', '2026-09-21')
            ->assertJsonPath('data.total_minutes', 120);
    }

    /** @test */
    public function it_returns_422_when_content_is_missing()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'hours' => 1,
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function it_returns_422_when_content_is_too_long()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => str_repeat('x', 1001),
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function it_returns_422_when_end_time_is_before_start_time()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Impossible',
            'start_time' => '2026-09-21 10:00:00',
            'end_time' => '2026-09-21 09:00:00',
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function it_returns_422_when_only_one_of_start_or_end_time_is_given()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Half a time',
            'start_time' => '2026-09-21 10:00:00',
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function it_returns_422_for_out_of_range_hours_and_minutes()
    {
        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Bad',
            'hours' => 24,
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['hours']);

        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Bad',
            'minutes' => 60,
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['minutes']);
    }

    /** @test */
    public function a_customer_cannot_create_comments_on_a_foreign_task()
    {
        $foreignTask = Task::factory()->create();
        $customer = $this->makeCustomerUser($this->task);

        $this->postJson("/api/tasks/{$foreignTask->id}/comments", [
            'content' => 'Nope',
        ], $this->headers($customer->generateApiKey()))
            ->assertForbidden();

        $this->assertDatabaseCount('task_notes', 0);
    }

    /** @test */
    public function a_customer_can_create_comments_on_their_tasks()
    {
        $customer = $this->makeCustomerUser($this->task);

        $this->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'Customer note',
        ], $this->headers($customer->generateApiKey()))
            ->assertCreated()
            ->assertJsonPath('data.content', 'Customer note');

        // And the note shows up in the list
        $this->getJson("/api/tasks/{$this->task->id}/comments", $this->headers($customer->generateApiKey()))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
