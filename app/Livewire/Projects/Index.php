<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $showCreateModal = false;
    public $name = '';
    public $description = '';
    public $viewMode = 'cards'; // 'cards' or 'list'
    public $showArchived = false; // Toggle to show archived projects

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        // Load view mode preference from session
        $this->viewMode = session('projects_view_mode', 'cards');
        $this->showArchived = session('projects_show_archived', false);
    }

    public function createProject()
    {
        $this->validate();

        Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['name', 'description', 'showCreateModal']);
        session()->flash('message', 'Project created successfully!');
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->reset(['name', 'description', 'showCreateModal']);
    }

    public function deleteProject($projectId)
    {
        // Only admins can delete projects
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            session()->flash('message', 'Only administrators can delete projects.');
            return;
        }

        $project = Project::findOrFail($projectId);

        // Delete all related data first
        $project->tasks()->each(function ($task) {
            $task->timeEntries()->delete();
            $task->notes()->delete();
            $task->delete();
        });

        // Delete the project
        $project->delete();

        session()->flash('message', 'Project and all related data deleted successfully.');
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        // Store preference in session
        session(['projects_view_mode' => $mode]);
    }

    public function toggleArchivedView()
    {
        $this->showArchived = !$this->showArchived;
        session(['projects_show_archived' => $this->showArchived]);
    }

    public function archiveProject($projectId)
    {
        // Only admins can archive projects
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            session()->flash('message', 'Only administrators can archive projects.');
            return;
        }

        $project = Project::findOrFail($projectId);
        $project->archive();

        session()->flash('message', 'Project archived successfully.');
    }

    public function unarchiveProject($projectId)
    {
        // Only admins can unarchive projects
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            session()->flash('message', 'Only administrators can unarchive projects.');
            return;
        }

        $project = Project::findOrFail($projectId);
        $project->unarchive();

        session()->flash('message', 'Project unarchived successfully.');
    }

    public function render()
    {
        $query = Project::with(['creator', 'tasks', 'customer'])
            ->withCount('tasks')
            ->orderBy('created_at', 'desc');

        if ($this->showArchived) {
            $projects = $query->archived()->get();
        } else {
            $projects = $query->active()->get();
        }

        return view('livewire.projects.index', [
            'projects' => $projects,
        ]);
    }
}
