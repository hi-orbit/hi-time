<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\TaskNote;
use App\Models\TaskAttachment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Show extends Component
{
    use WithFileUploads;

    public Project $project;
    public $showTaskModal = false;
    public $showTimeModal = false;
    public $showTaskDetailsModal = false;
    public $showMoveTaskModal = false;
    public $selectedTask = null;
    public $editingTask = false;

    // Search functionality
    public $searchQuery = '';
    public $showSearchResults = false;
    public $searchResults = [];

    // Task creation/editing fields
    public $title = '';
    public $description = '';
    public $assigned_to = '';
    public $status = 'backlog';

    // Move task fields
    public $moveToProjectId = '';

    // Time tracking fields
    public $timeDescription = '';
    public $hours = '';
    public $minutes = '';
    public $activityType = '';
    public $isGeneralActivity = false;

    // Task notes fields
    public $newNote = '';
    public $newNoteHours = '';
    public $newNoteMinutes = '';

    // File upload fields
    public $attachmentFiles = [];
    public $singleAttachment = null;
    public $dropzoneFiles = [];

    public function updatedAttachmentFiles()
    {
        Log::info('Files updated in Livewire', [
            'count' => count($this->attachmentFiles),
            'files' => array_map(function($file) {
                if ($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
                return null;
            }, $this->attachmentFiles)
        ]);
    }

    public function updatedSingleAttachment()
    {
        Log::info('Single file updated in Livewire', [
            'file' => $this->singleAttachment ? [
                'name' => $this->singleAttachment->getClientOriginalName(),
                'size' => $this->singleAttachment->getSize(),
                'type' => $this->singleAttachment->getMimeType()
            ] : null
        ]);
    }

    // Quick assignment field
    public $taskAssignment = '';

    // Task status editing (for details modal)
    public $taskStatus = '';

    // Time tracking form visibility
    public $showTimeForm = false;

    protected $rules = [
        'newNote' => 'required|string|max:1000',
        'newNoteHours' => 'nullable|integer|min:0|max:23',
        'newNoteMinutes' => 'nullable|integer|min:0|max:59',
        // Note: dropzoneFiles validation is handled manually in processDropzoneFiles()
    ];

    public function mount(Project $project)
    {
        // Prevent access to archived projects unless user is admin
        if ($project->archived && (!Auth::user() || Auth::user()->role !== 'admin')) {
            abort(404);
        }

        $this->project = $project->load('customer');

        // Check for task parameter in URL to auto-open task details
        if (request()->has('task')) {
            $taskId = request()->get('task');
            $task = Task::where('id', $taskId)->where('project_id', $project->id)->first();

            if ($task) {
                $this->openTaskDetails($taskId);
            }
        }

        Log::info('Projects.Show component mounted', [
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'withFileUploads_trait' => trait_exists('Livewire\WithFileUploads') ? 'exists' : 'not_exists'
        ]);
    }

    private function getNotificationService()
    {
        return new NotificationService();
    }

    public function getStandardActivityTypes()
    {
        return [
            'Client Meeting',
            'Project Planning',
            'Research',
            'Documentation',
            'Code Review',
            'Testing',
            'Deployment',
            'Training',
            'Administrative',
            'Travel Time',
            'Bug Investigation',
            'System Maintenance',
            'Client Communication',
            'Team Meeting',
            'Other'
        ];
    }

    // Drag and drop functionality
    public function updateTaskOrder($taskId, $newStatus, $targetTaskId = null, $position = 'after')
    {
        $task = Task::findOrFail($taskId);
        $oldStatus = $task->status;

        // Update task status
        $task->update(['status' => $newStatus]);

        // Send notification for status changes to assigned user
        if ($newStatus != $oldStatus && $task->assigned_to) {
            $this->getNotificationService()->sendTaskStatusNotification(
                $task->assigned_to,
                $task->title,
                $newStatus,
                $this->project->name,
                $this->project->id
            );
        }

        // Get all tasks in the new column
        $tasksInColumn = Task::where('project_id', $this->project->id)
            ->where('status', $newStatus)
            ->orderBy('order')
            ->get();

        // If we have a target task, insert relative to it
        if ($targetTaskId) {
            $targetTask = Task::find($targetTaskId);
            if ($targetTask && $targetTask->status === $newStatus) {
                $this->insertTaskAtPosition($task, $targetTask, $position, $tasksInColumn);
            } else {
                $this->appendTaskToColumn($task, $tasksInColumn);
            }
        } else {
            // No target, append to end
            $this->appendTaskToColumn($task, $tasksInColumn);
        }

        // Reorder tasks in both old and new columns
        if ($oldStatus !== $newStatus) {
            $this->reorderTasks($oldStatus);
        }
        $this->reorderTasks($newStatus);
    }

    private function insertTaskAtPosition($task, $targetTask, $position, $tasksInColumn)
    {
        $targetOrder = $targetTask->order;

        if ($position === 'before') {
            $newOrder = $targetOrder;
        } else {
            $newOrder = $targetOrder + 1;
        }

        // Shift other tasks down
        foreach ($tasksInColumn as $columnTask) {
            if ($columnTask->id !== $task->id && $columnTask->order >= $newOrder) {
                $columnTask->update(['order' => $columnTask->order + 1]);
            }
        }

        $task->update(['order' => $newOrder]);
    }

    private function appendTaskToColumn($task, $tasksInColumn)
    {
        $maxOrder = $tasksInColumn->where('id', '!=', $task->id)->max('order') ?? -1;
        $task->update(['order' => $maxOrder + 1]);
    }

    private function reorderTasks($status)
    {
        $tasks = Task::where('project_id', $this->project->id)
            ->where('status', $status)
            ->orderBy('order')
            ->get();

        foreach ($tasks as $index => $task) {
            $task->update(['order' => $index]);
        }
    }

    // Task details and notes
    public function openTaskDetails($taskId)
    {
        $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($taskId);
        $this->taskAssignment = $this->selectedTask->assigned_to;
        $this->taskStatus = $this->selectedTask->status;

        // Initialize time tracking fields
        $this->timeDescription = '';
        $this->hours = '';
        $this->minutes = '';
        $this->isGeneralActivity = false; // This is for a specific task

        $this->showTaskDetailsModal = true;
    }

    public function closeTaskDetailsModal()
    {
        $this->reset(['showTaskDetailsModal', 'selectedTask', 'newNote', 'newNoteHours', 'newNoteMinutes', 'taskAssignment', 'taskStatus', 'showTimeForm', 'timeDescription', 'hours', 'minutes', 'attachmentFiles', 'dropzoneFiles']);
    }

    public function addNote()
    {
        $this->validate([
            'newNote' => 'required|string|max:1000',
            'newNoteHours' => 'nullable|integer|min:0|max:23',
            'newNoteMinutes' => 'nullable|integer|min:0|max:59',
        ]);

        if ($this->selectedTask) {
            $hours = (int) $this->newNoteHours ?: 0;
            $minutes = (int) $this->newNoteMinutes ?: 0;
            $totalMinutes = ($hours * 60) + $minutes;

            // Create the note with time tracking data
            TaskNote::create([
                'task_id' => $this->selectedTask->id,
                'user_id' => Auth::id(),
                'content' => $this->newNote,
                'hours' => $hours > 0 ? $hours : null,
                'minutes' => $minutes > 0 ? $minutes : null,
                'total_minutes' => $totalMinutes > 0 ? $totalMinutes : null,
            ]);

            // If time was logged, also create a time entry for backwards compatibility
            if ($totalMinutes > 0) {
                TimeEntry::create([
                    'task_id' => $this->selectedTask->id,
                    'user_id' => Auth::id(),
                    'description' => $this->newNote,
                    'duration_minutes' => $totalMinutes,
                    'is_running' => false,
                    'project_id' => $this->project->id,
                ]);
            }

            // Refresh the selected task to show the new note and maintain all relationships
            $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($this->selectedTask->id);

            // Reset form fields
            $this->reset(['newNote', 'newNoteHours', 'newNoteMinutes']);

            // Use dispatch instead of session flash to avoid component refresh issues
            $this->dispatch('note-added', message: 'Note added successfully!');
        }
    }

    public function deleteNote($noteId)
    {
        $note = TaskNote::find($noteId);

        if ($note && $note->task_id === $this->selectedTask->id) {
            // Only allow deletion by the note creator
            if ($note->user_id === Auth::id()) {
                $note->delete();

                // Refresh the selected task to show updated notes
                $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($this->selectedTask->id);

                $this->dispatch('note-deleted', message: 'Note deleted successfully!');
            } else {
                $this->dispatch('note-error', message: 'You can only delete your own notes.');
            }
        } else {
            $this->dispatch('note-error', message: 'Note not found.');
        }
    }    public function createTask()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:backlog,in_progress,in_test,ready_to_release,done',
        ]);

        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'project_id' => $this->project->id,
            'assigned_to' => $this->assigned_to ?: null,
            'created_by' => Auth::id(),
        ]);

        // Send push notification if task is assigned to someone
        if ($this->assigned_to) {
            $this->getNotificationService()->sendTaskAssignmentNotification(
                $this->assigned_to,
                $this->title,
                $this->project->name,
                $this->project->id
            );
        }

        $this->reset(['title', 'description', 'assigned_to', 'status', 'showTaskModal']);
        session()->flash('message', 'Task created successfully!');
    }

    public function editTask($taskId)
    {
        $task = Task::findOrFail($taskId);

        // Populate form fields with existing task data
        $this->title = $task->title;
        $this->description = $task->description;
        $this->assigned_to = $task->assigned_to;
        $this->status = $task->status;
        $this->moveToProjectId = ''; // Initialize as empty (keep in current project)
        $this->selectedTask = $task;
        $this->editingTask = true;

        // Close task details modal and open edit modal
        $this->showTaskDetailsModal = false;
        $this->showTaskModal = true;
    }

    public function updateTask()
    {
        try {
            $this->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'assigned_to' => 'nullable|exists:users,id',
                'status' => 'required|in:backlog,in_progress,in_test,failed_testing,ready_to_release,done',
                'moveToProjectId' => 'nullable|exists:projects,id',
            ]);

            $oldAssignedTo = $this->selectedTask->assigned_to;
            $oldStatus = $this->selectedTask->status;
            $oldProjectId = $this->selectedTask->project_id;

            // Update basic task fields
            $this->selectedTask->update([
                'title' => $this->title,
                'description' => $this->description,
                'assigned_to' => $this->assigned_to ?: null,
                'status' => $this->status,
            ]);

            // Handle project move if specified
            if ($this->moveToProjectId && $this->moveToProjectId != $oldProjectId) {
                $newProject = Project::find($this->moveToProjectId);

                if ($newProject) {
                    // Update the task's project
                    $this->selectedTask->update(['project_id' => $this->moveToProjectId]);

                    // Clear the selectedTask since it's no longer in this project
                    $this->selectedTask = null;

                    session()->flash('message', 'Task moved to "' . $newProject->name . '" successfully.');

                    // Close the modal and refresh the page
                    $this->reset(['title', 'description', 'assigned_to', 'status', 'showTaskModal', 'editingTask', 'moveToProjectId']);
                    $this->mount($this->project);
                    return;
                }
            }

            // Send notifications for assignment changes
            if ($this->assigned_to != $oldAssignedTo) {
                // If task was reassigned to someone new
                if ($this->assigned_to) {
                    $this->getNotificationService()->sendTaskAssignmentNotification(
                        $this->assigned_to,
                        $this->title,
                        $this->project->name,
                        $this->project->id
                    );
                }
            }

            // Send notification for status changes to assigned user
            if ($this->status != $oldStatus && $this->assigned_to) {
                $this->getNotificationService()->sendTaskStatusNotification(
                    $this->assigned_to,
                    $this->title,
                    $this->status,
                    $this->project->name,
                    $this->project->id
                );
            }

            // Refresh the task data for task details modal
            $taskId = $this->selectedTask->id;

            // Close the edit modal and reset form
            $this->reset(['title', 'description', 'assigned_to', 'status', 'showTaskModal', 'editingTask']);

            // Reopen task details modal with updated data
            $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($taskId);
            $this->taskAssignment = $this->selectedTask->assigned_to;
            $this->showTaskDetailsModal = true;

            session()->flash('message', 'Task updated successfully!');
        } catch (\Exception $e) {
            session()->flash('message', 'Error updating task: ' . $e->getMessage());
        }
    }

    public function updateTaskAssignment()
    {
        $this->validate([
            'taskAssignment' => 'nullable|exists:users,id',
        ]);

        if ($this->selectedTask) {
            $oldAssignedTo = $this->selectedTask->assigned_to;

            $this->selectedTask->update([
                'assigned_to' => $this->taskAssignment ?: null,
            ]);

            // Send notification if task was reassigned to someone new
            if ($this->taskAssignment != $oldAssignedTo && $this->taskAssignment) {
                $this->getNotificationService()->sendTaskAssignmentNotification(
                    $this->taskAssignment,
                    $this->selectedTask->title,
                    $this->project->name,
                    $this->project->id
                );
            }

            // Refresh the task data
            $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($this->selectedTask->id);

            // Use dispatch instead of session flash
            $this->dispatch('assignment-updated', message: 'Task assignment updated successfully!');
        }
    }

    public function updateTaskStatusFromModal()
    {
        $this->validate([
            'taskStatus' => 'required|in:backlog,in_progress,in_test,failed_testing,ready_to_release,done',
        ]);

        if ($this->selectedTask) {
            $oldStatus = $this->selectedTask->status;

            $this->selectedTask->update([
                'status' => $this->taskStatus,
            ]);

            // Send notification if status changed and task is assigned
            if ($this->taskStatus !== $oldStatus && $this->selectedTask->assigned_to) {
                $this->getNotificationService()->sendTaskStatusNotification(
                    $this->selectedTask->assigned_to,
                    $this->selectedTask->title,
                    $this->taskStatus,
                    $this->project->name,
                    $this->project->id
                );
            }

            // Refresh the task data
            $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries', 'attachments.uploader'])->findOrFail($this->selectedTask->id);

            $this->dispatch('status-updated', message: 'Task status updated successfully!');
        }
    }

    public function copyShareableLink($taskId)
    {
        $task = Task::findOrFail($taskId);
        $shareableUrl = url("/projects/{$task->project_id}?task={$task->id}");

        // We'll use JavaScript to copy to clipboard
        $this->dispatch('copy-to-clipboard', url: $shareableUrl, message: 'Shareable link copied to clipboard!');
    }

    public function toggleTimeForm()
    {
        $this->showTimeForm = !$this->showTimeForm;

        // Reset time fields when hiding the form
        if (!$this->showTimeForm) {
            $this->timeDescription = '';
            $this->hours = '';
            $this->minutes = '';
        }
    }

    public function deleteTask($taskId)
    {
        $task = Task::findOrFail($taskId);

        // Users can delete tasks assigned to them or tasks they created, admins can delete any task
        $user = Auth::user();
        if (!$user ||
            ($user->role !== 'admin' &&
             $task->assigned_to !== $user->id &&
             $task->created_by !== $user->id)) {
            session()->flash('message', 'You do not have permission to delete this task.');
            return;
        }

        // Delete all related data first
        $task->timeEntries()->delete();
        $task->notes()->delete();

        // Delete the task
        $task->delete();

        // Close task details modal if it was open
        if ($this->selectedTask && $this->selectedTask->id === $taskId) {
            $this->closeTaskDetailsModal();
        }

        session()->flash('message', 'Task deleted successfully.');
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => $newStatus]);
    }

    // Search functionality
    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 2) {
            $this->performSearch();
            $this->showSearchResults = true;
        } else {
            $this->searchResults = [];
            $this->showSearchResults = false;
        }
    }

    public function performSearch()
    {
        $this->searchResults = Task::where('project_id', $this->project->id)
            ->where(function ($query) {
                $query->where('title', 'LIKE', '%' . $this->searchQuery . '%')
                      ->orWhere('description', 'LIKE', '%' . $this->searchQuery . '%');
            })
            ->with(['assignedUser'])
            ->orderBy('title')
            ->get();
    }

    public function selectSearchResult($taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->selectedTask = $task;
        $this->showTaskDetailsModal = true;
        $this->searchQuery = '';
        $this->showSearchResults = false;
        $this->searchResults = [];
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->showSearchResults = false;
        $this->searchResults = [];
    }

    public function startTimer($taskId)
    {
        $task = Task::findOrFail($taskId);

        // Stop any running timers for this user
        TimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->update([
                'is_running' => false,
                'end_time' => now(),
            ]);

        // Start new timer
        TimeEntry::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'start_time' => now(),
            'is_running' => true,
        ]);

        session()->flash('message', 'Timer started for: ' . $task->title);
    }

    public function stopTimer($taskId)
    {
        $runningEntries = TimeEntry::where('task_id', $taskId)
            ->where('user_id', Auth::id())
            ->where('is_running', true)
            ->get();

        foreach ($runningEntries as $entry) {
            $endTime = now();
            $durationMinutes = $entry->start_time->diffInMinutes($endTime);

            $entry->update([
                'is_running' => false,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
            ]);
        }

        session()->flash('message', 'Timer stopped.');
    }

    public function openTaskModal()
    {
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->reset(['title', 'description', 'assigned_to', 'status', 'showTaskModal', 'editingTask', 'selectedTask']);
    }

    public function openTimeModal($taskId = null)
    {
        if ($taskId) {
            $this->selectedTask = Task::findOrFail($taskId);
            $this->isGeneralActivity = false;
        } else {
            $this->selectedTask = null;
            $this->isGeneralActivity = true;
        }
        $this->showTimeModal = true;
    }

    public function closeTimeModal()
    {
        $this->reset(['timeDescription', 'hours', 'minutes', 'showTimeModal', 'selectedTask', 'activityType', 'isGeneralActivity']);
    }

    public function logTime()
    {
        // Enhanced validation that includes activity type for general activities
        $rules = [
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'required|integer|min:0|max:59',
            'timeDescription' => 'nullable|string|max:255',
        ];

        if ($this->isGeneralActivity) {
            $rules['activityType'] = 'required|string|max:100';
        }

        $this->validate($rules);

        // Treat empty or null hours as 0
        $hours = $this->hours ?: 0;
        $totalMinutes = ($hours * 60) + $this->minutes;

        if ($totalMinutes > 0) {
            $timeEntryData = [
                'user_id' => Auth::id(),
                'description' => $this->timeDescription,
                'duration_minutes' => $totalMinutes,
                'is_running' => false,
            ];

            if ($this->isGeneralActivity) {
                // For general activities
                $timeEntryData['activity_type'] = $this->activityType;
                $timeEntryData['project_id'] = $this->project->id;
                $timeEntryData['task_id'] = null;
            } else {
                // For task-specific activities
                $timeEntryData['task_id'] = $this->selectedTask->id;
                $timeEntryData['project_id'] = $this->project->id;
                $timeEntryData['activity_type'] = null;
            }

            try {
                $timeEntry = TimeEntry::create($timeEntryData);

                if ($timeEntry) {
                    $activityLabel = $this->isGeneralActivity ? $this->activityType : $this->selectedTask->title;
                    session()->flash('message', "Time logged successfully for: {$activityLabel}!");
                } else {
                    session()->flash('error', 'Failed to save time entry. Please try again.');
                }
            } catch (\Exception $e) {
                Log::error('Time entry creation failed: ' . $e->getMessage());
                session()->flash('error', 'Error saving time entry: ' . $e->getMessage());
            }

            $this->closeTimeModal();
        } else {
            session()->flash('error', 'Please enter a valid time duration.');
        }
    }

    public function logTimeInline()
    {
        // Validation for inline time logging
        $this->validate([
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'required|integer|min:0|max:59',
            'timeDescription' => 'nullable|string|max:255',
        ]);

        // Treat empty or null hours as 0
        $hours = $this->hours ?: 0;
        $totalMinutes = ($hours * 60) + $this->minutes;

        if ($totalMinutes > 0) {
            $timeEntryData = [
                'user_id' => Auth::id(),
                'description' => $this->timeDescription,
                'duration_minutes' => $totalMinutes,
                'is_running' => false,
                'task_id' => $this->selectedTask->id,
                'project_id' => $this->project->id,
                'activity_type' => null,
            ];

            try {
                $timeEntry = TimeEntry::create($timeEntryData);

                if ($timeEntry) {
                    // Reset the time fields and hide the form
                    $this->timeDescription = '';
                    $this->hours = '';
                    $this->minutes = '';
                    $this->showTimeForm = false;

                    // Refresh the selected task to show updated time entries
                    $this->selectedTask = Task::with(['notes.user', 'assignedUser', 'timeEntries.user', 'attachments.uploader'])->findOrFail($this->selectedTask->id);

                    // Dispatch success event for Alpine.js
                    $this->dispatch('time-logged', message: 'Time logged successfully!');
                } else {
                    $this->dispatch('time-log-error', message: 'Failed to save time entry. Please try again.');
                }
            } catch (\Exception $e) {
                Log::error('Time entry creation failed: ' . $e->getMessage());
                $this->dispatch('time-log-error', message: 'Error saving time entry: ' . $e->getMessage());
            }
        } else {
            $this->dispatch('time-log-error', message: 'Please enter a valid time duration.');
        }
    }

    public function uploadSingleAttachment()
    {
        Log::info('Single upload method called', [
            'selectedTask' => $this->selectedTask ? $this->selectedTask->id : 'null',
            'singleAttachment' => $this->singleAttachment ? 'file exists' : 'null',
        ]);

        if (!$this->selectedTask) {
            session()->flash('error', 'No task selected.');
            return;
        }

        if (!$this->singleAttachment) {
            session()->flash('error', 'No file selected for upload.');
            return;
        }

        try {
            // Simplified validation
            $this->validate([
                'singleAttachment' => 'required|file|max:2048',
            ]);

            Log::info('Single file validation passed');

            $file = $this->singleAttachment;
            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            Log::info('About to store single file', ['fileName' => $fileName]);

            $filePath = $file->storeAs('task-attachments', $fileName, 'public');

            Log::info('Single file stored', ['filePath' => $filePath]);

            TaskAttachment::create([
                'task_id' => $this->selectedTask->id,
                'uploaded_by' => Auth::id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            Log::info('Single file database record created');

            $this->singleAttachment = null;
            $this->selectedTask->load('attachments.uploader');
            session()->flash('message', "Successfully uploaded file: {$originalName}!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Single file validation error', [
                'errors' => $e->validator->errors()->all(),
                'task_id' => $this->selectedTask->id
            ]);
            session()->flash('error', 'Upload failed: ' . implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            Log::error('Single file upload exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $this->selectedTask->id
            ]);
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function uploadAttachments()
    {
        Log::info('Upload method called', [
            'selectedTask' => $this->selectedTask ? $this->selectedTask->id : 'null',
            'attachmentFiles' => $this->attachmentFiles ? count($this->attachmentFiles) : 'null',
            'files_array' => $this->attachmentFiles
        ]);

        if (!$this->selectedTask) {
            session()->flash('error', 'No task selected.');
            return;
        }

        if (empty($this->attachmentFiles)) {
            session()->flash('error', 'No files selected for upload.');
            return;
        }

        try {
            // Simplified validation first
            $this->validate([
                'attachmentFiles.*' => 'required|file|max:2048', // Remove mime types for now
            ]);

            Log::info('Validation passed');

            $uploadedCount = 0;
            foreach ($this->attachmentFiles as $index => $file) {
                Log::info('Processing file', [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension()
                ]);

                $originalName = $file->getClientOriginalName();
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

                Log::info('About to store file', ['fileName' => $fileName]);

                $filePath = $file->storeAs('task-attachments', $fileName, 'public');

                Log::info('File stored', ['filePath' => $filePath]);

                TaskAttachment::create([
                    'task_id' => $this->selectedTask->id,
                    'uploaded_by' => Auth::id(),
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                Log::info('Database record created');
                $uploadedCount++;
            }

            $this->attachmentFiles = [];
            $this->selectedTask->load('attachments.uploader');
            session()->flash('message', "Successfully uploaded {$uploadedCount} file(s)!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error during upload', [
                'errors' => $e->validator->errors()->all(),
                'task_id' => $this->selectedTask->id
            ]);
            session()->flash('error', 'Upload failed: ' . implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            Log::error('Exception during upload', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $this->selectedTask->id
            ]);
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function refreshAttachments()
    {
        if ($this->selectedTask) {
            $this->selectedTask->load('attachments.uploader');
        }
    }

    public function processDropzoneFiles()
    {
        Log::info('Process dropzone files called', [
            'count' => count($this->dropzoneFiles ?? []),
            'selectedTask' => $this->selectedTask ? $this->selectedTask->id : 'null',
            'files_debug' => $this->dropzoneFiles
        ]);

        if (!$this->selectedTask) {
            session()->flash('error', 'No task selected.');
            return;
        }

        if (empty($this->dropzoneFiles)) {
            session()->flash('error', 'No files to upload.');
            return;
        }

        $uploadedCount = 0;
        $errorCount = 0;

        foreach ($this->dropzoneFiles as $index => $fileData) {
            try {
                if (!$fileData || !is_array($fileData)) {
                    Log::warning('Invalid file data at index: ' . $index, ['data' => $fileData]);
                    $errorCount++;
                    continue;
                }

                // The dropzone component provides file data as an array with keys like:
                // 'tmpFilename', 'name', 'extension', 'path', 'temporaryUrl', 'size'
                if (!isset($fileData['tmpFilename']) || !isset($fileData['name'])) {
                    Log::warning('Missing required file data', ['fileData' => $fileData]);
                    $errorCount++;
                    continue;
                }

                // Check file size limits based on file type
                $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];
                $extension = strtolower($fileData['extension'] ?? '');
                $isVideo = in_array($extension, $videoExtensions);
                $fileSize = $fileData['size'] ?? 0;

                // Size validation
                $maxSizeBytes = $isVideo ? 20 * 1024 * 1024 : 10 * 1024 * 1024; // 20MB vs 10MB
                if ($fileSize > $maxSizeBytes) {
                    $maxSizeMB = $isVideo ? 20 : 10;
                    $actualSizeMB = round($fileSize / 1024 / 1024, 2);
                    Log::warning("File too large: {$fileData['name']} - {$actualSizeMB}MB exceeds {$maxSizeMB}MB limit");
                    session()->flash('error', "File '{$fileData['name']}' is too large ({$actualSizeMB}MB). " .
                        ($isVideo ? "Videos" : "Files") . " must be under {$maxSizeMB}MB.");
                    $errorCount++;
                    continue;
                }

                // Create a temporary uploaded file from the path
                $tempFile = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileData['tmpFilename']);

                if (!$tempFile) {
                    Log::error('Could not create temporary file', ['fileData' => $fileData]);
                    $errorCount++;
                    continue;
                }

                $originalName = $fileData['name'];
                $fileName = Str::uuid() . '.' . ($fileData['extension'] ?? 'bin');

                Log::info('Processing dropzone file', [
                    'fileName' => $fileName,
                    'originalName' => $originalName,
                    'size' => $fileData['size'] ?? 'unknown',
                    'tmpFilename' => $fileData['tmpFilename']
                ]);

                $filePath = $tempFile->storeAs('task-attachments', $fileName, 'public');

                TaskAttachment::create([
                    'task_id' => $this->selectedTask->id,
                    'uploaded_by' => Auth::id(),
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $tempFile->getMimeType(),
                    'file_size' => $tempFile->getSize(),
                ]);

                Log::info('Dropzone file saved', ['fileName' => $fileName]);
                $uploadedCount++;

            } catch (\Exception $e) {
                Log::error('Error processing dropzone file', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'fileData' => $fileData ?? 'null',
                    'index' => $index
                ]);
                $errorCount++;
            }
        }

        // Clear the files array and refresh attachments
        $this->dropzoneFiles = [];
        $this->refreshAttachments();

        if ($uploadedCount > 0) {
            $this->dispatch('files-uploaded', message: "Successfully uploaded {$uploadedCount} file(s)!");
        }

        if ($errorCount > 0) {
            $this->dispatch('upload-error', message: "Failed to upload {$errorCount} file(s). Check logs for details.");
        }
    }

    public function updatedDropzoneFiles()
    {
        // Auto-process files when they are added
        Log::info('Dropzone files updated - auto-processing', [
            'count' => count($this->dropzoneFiles ?? []),
            'selectedTask' => $this->selectedTask ? $this->selectedTask->id : 'null',
            'files_details' => array_map(function($file) {
                if (is_array($file)) {
                    return [
                        'name' => $file['name'] ?? 'unknown',
                        'size' => $file['size'] ?? 'unknown',
                        'extension' => $file['extension'] ?? 'unknown'
                    ];
                }
                return 'not_array';
            }, $this->dropzoneFiles ?? [])
        ]);

        if (!empty($this->dropzoneFiles)) {
            try {
                // Automatically process the files
                $this->processDropzoneFiles();
            } catch (\Exception $e) {
                Log::error('Error in updatedDropzoneFiles', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                session()->flash('error', 'Upload failed: ' . $e->getMessage());
            }
        }

        // Force a re-render to update the UI
        $this->dispatch('$refresh');
    }

    public function refreshDropzoneState()
    {
        // Manual method to refresh the state if needed
        Log::info('Manual dropzone refresh called', [
            'dropzoneFiles_count' => count($this->dropzoneFiles ?? [])
        ]);
        $this->dispatch('$refresh');
    }

    /**
     * Livewire lifecycle hook - called before a property is updated
     */
    public function updatingDropzoneFiles($value)
    {
        Log::info('updatingDropzoneFiles called', [
            'value_type' => gettype($value),
            'value_count' => is_array($value) ? count($value) : 'not_array',
            'value_preview' => is_array($value) ? array_slice($value, 0, 2) : $value
        ]);

        // Don't perform validation here - let the files through to updatedDropzoneFiles
        // where we handle custom validation in processDropzoneFiles()
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = TaskAttachment::find($attachmentId);

        if ($attachment && $attachment->task_id === $this->selectedTask->id) {
            // Check if user can delete (either uploader or admin)
            $user = Auth::user();
            if ($attachment->uploaded_by === Auth::id() || ($user && $user->role === 'admin')) {
                // Delete file from storage
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }

                $attachment->delete();
                $this->selectedTask->load('attachments.uploader');
                $this->dispatch('attachment-deleted', message: 'Attachment deleted successfully!');
            } else {
                $this->dispatch('attachment-error', message: 'You do not have permission to delete this attachment.');
            }
        }
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = TaskAttachment::find($attachmentId);

        if ($attachment && $attachment->task_id === $this->selectedTask->id) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                return response()->download(
                    Storage::disk('public')->path($attachment->file_path),
                    $attachment->original_name
                );
            }
        }

        session()->flash('error', 'File not found!');
    }

    public function render()
    {
        $columns = [
            'backlog' => $this->project->tasks()->where('status', 'backlog')->orderBy('order')->get(),
            'in_progress' => $this->project->tasks()->where('status', 'in_progress')->orderBy('order')->get(),
            'in_test' => $this->project->tasks()->where('status', 'in_test')->orderBy('order')->get(),
            'failed_testing' => $this->project->tasks()->where('status', 'failed_testing')->orderBy('order')->get(),
            'ready_to_release' => $this->project->tasks()->where('status', 'ready_to_release')->orderBy('order')->get(),
            'done' => $this->project->tasks()->where('status', 'done')->orderBy('order')->get(),
        ];

        $users = User::all();

        return view('livewire.projects.show', [
            'columns' => $columns,
            'users' => $users,
        ]);
    }
}
