<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskNote;
use App\Livewire\Projects\Show;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskRichTextTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $project;
    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Customer user: skips the MySQL-only TIMESTAMPDIFF "total time" blocks,
        // which the SQLite test database cannot execute. Description, notes and
        // the edit modal still render for customers.
        $this->user = User::factory()->create(['role' => 'customer']);
        $this->project = Project::factory()->create();
        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'description' => '<p>Hello <b>world</b></p>',
            'created_by' => $this->user->id,
        ]);

        TaskNote::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => '<p>Note <em>body</em></p>',
        ]);

        TaskNote::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => "line1\nline2",
        ]);
    }

    /** @test */
    public function it_renders_html_description_and_notes_in_task_details()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Show::class, ['project' => $this->project])
            ->call('openTaskDetails', $this->task->id);

        // HTML description renders as raw HTML (formatting respected)
        $component->assertSeeHtml('<p>Hello <b>world</b></p>');

        // HTML note renders as raw HTML
        $component->assertSeeHtml('<p>Note <em>body</em></p>');

        // Legacy plain-text note keeps its line break via nl2br
        $component->assertSeeHtml('<br />');
        $component->assertSee('line1', false);
        $component->assertSee('line2', false);
    }

    /** @test */
    public function it_renders_plain_text_description_fallback_when_empty()
    {
        $this->task->update(['description' => null]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['project' => $this->project])
            ->call('openTaskDetails', $this->task->id)
            ->assertSee('No description provided.');
    }

    /** @test */
    public function it_renders_sun_editor_for_task_description_and_notes()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Show::class, ['project' => $this->project])
            ->call('openTaskDetails', $this->task->id);

        // Note editor markup in the details modal
        $component->assertSeeHtml('id="note-editor"');
        $component->assertSeeHtml('id="new-note-input"');

        // Description editor in the create/edit task modal
        $component
            ->call('editTask', $this->task->id)
            ->assertSeeHtml('id="task-description-editor"')
            ->assertSeeHtml('id="task-description-input"');
    }

    /** @test */
    public function it_saves_html_note_content()
    {
        Livewire::actingAs($this->user)
            ->test(Show::class, ['project' => $this->project])
            ->call('openTaskDetails', $this->task->id)
            ->set('newNote', '<p>Bold <b>idea</b></p>')
            ->call('addNote');

        $this->assertDatabaseHas('task_notes', [
            'task_id' => $this->task->id,
            'content' => '<p>Bold <b>idea</b></p>',
        ]);
    }
}
