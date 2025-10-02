<?php

namespace App\Console\Commands;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupCompletedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cleanup-completed
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--hours=24 : Number of hours after which completed tasks should be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tasks that have been in "Done" status for more than 24 hours
    
    This command is scheduled to run daily at 2 AM. To enable scheduling in production,
    add this to your crontab: * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $cutoffTime = Carbon::now()->subHours($hours);

        $this->info("Looking for tasks in 'Done' status older than {$hours} hours (before {$cutoffTime->format('Y-m-d H:i:s')})...");

        // Find tasks that are in "Done" status and have been updated more than X hours ago
        $tasksToDelete = Task::where('status', 'done')
            ->where('updated_at', '<', $cutoffTime)
            ->with(['project', 'notes', 'attachments', 'tags', 'timeEntries'])
            ->get();

        if ($tasksToDelete->isEmpty()) {
            $this->info('No completed tasks found for cleanup.');
            return Command::SUCCESS;
        }

        $this->info("Found {$tasksToDelete->count()} completed tasks to delete:");

        foreach ($tasksToDelete as $task) {
            $this->line("- Task ID: {$task->id} | Title: {$task->title} | Project: {$task->project->name} | Updated: {$task->updated_at->format('Y-m-d H:i:s')}");
        }

        if ($dryRun) {
            $this->warn('DRY RUN: No tasks were actually deleted. Remove --dry-run flag to perform the deletion.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Are you sure you want to delete these tasks? This action cannot be undone.')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($tasksToDelete as $task) {
            try {
                // Log the deletion for audit purposes
                Log::info('Deleting completed task', [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'project_id' => $task->project_id,
                    'project_name' => $task->project->name,
                    'status' => $task->status,
                    'updated_at' => $task->updated_at,
                    'notes_count' => $task->notes->count(),
                    'attachments_count' => $task->attachments->count(),
                    'tags_count' => $task->tags->count(),
                    'time_entries_count' => $task->timeEntries->count(),
                ]);

                // Delete the task (this will cascade to related data due to foreign key constraints)
                $task->delete();
                $deletedCount++;

                $this->info("✓ Deleted task: {$task->title}");

            } catch (\Exception $e) {
                $errors[] = "Failed to delete task '{$task->title}' (ID: {$task->id}): " . $e->getMessage();
                Log::error('Failed to delete completed task', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully deleted {$deletedCount} completed tasks.");

        if (!empty($errors)) {
            $this->error('Some tasks could not be deleted:');
            foreach ($errors as $error) {
                $this->error("- {$error}");
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
