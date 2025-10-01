<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Assign existing tags to customers based on the projects they're used in
        $tags = DB::table('tags')->whereNull('customer_id')->get();

        foreach ($tags as $tag) {
            // Find the customer for this tag by looking at the projects of tasks that use this tag
            $customerIds = DB::table('task_tag')
                ->join('tasks', 'task_tag.task_id', '=', 'tasks.id')
                ->join('projects', 'tasks.project_id', '=', 'projects.id')
                ->where('task_tag.tag_id', $tag->id)
                ->whereNotNull('projects.customer_id')
                ->distinct()
                ->pluck('projects.customer_id');

            if ($customerIds->count() === 1) {
                // Tag is used only in projects of one customer
                DB::table('tags')
                    ->where('id', $tag->id)
                    ->update(['customer_id' => $customerIds->first()]);

                Log::info("Assigned tag '{$tag->name}' to customer ID {$customerIds->first()}");
            } elseif ($customerIds->count() > 1) {
                // Tag is used across multiple customers - need to duplicate it
                $originalTagId = $tag->id;

                foreach ($customerIds as $customerId) {
                    // Create a new tag for each customer except the first one
                    if ($customerId === $customerIds->first()) {
                        // Assign the original tag to the first customer
                        DB::table('tags')
                            ->where('id', $originalTagId)
                            ->update(['customer_id' => $customerId]);
                        continue;
                    }

                    // Create a duplicate tag for other customers
                    $newTagId = DB::table('tags')->insertGetId([
                        'name' => $tag->name,
                        'color' => $tag->color,
                        'description' => $tag->description,
                        'customer_id' => $customerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update task_tag relationships for tasks in this customer's projects
                    $taskIds = DB::table('tasks')
                        ->join('projects', 'tasks.project_id', '=', 'projects.id')
                        ->where('projects.customer_id', $customerId)
                        ->pluck('tasks.id');

                    // Update the task_tag relationships
                    DB::table('task_tag')
                        ->where('tag_id', $originalTagId)
                        ->whereIn('task_id', $taskIds)
                        ->update(['tag_id' => $newTagId]);

                    Log::info("Created duplicate tag '{$tag->name}' for customer ID {$customerId} with new tag ID {$newTagId}");
                }
            } else {
                // Tag is not used in any tasks with customer projects - could be orphaned
                Log::warning("Tag '{$tag->name}' (ID: {$tag->id}) is not used in any customer projects");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it involves data duplication
        // In a rollback scenario, you would need to manually clean up duplicate tags
        // and restore the original tag assignments
        Log::warning('Migration rollback for assign_existing_tags_to_customers is not implemented - manual cleanup required');
    }
};
