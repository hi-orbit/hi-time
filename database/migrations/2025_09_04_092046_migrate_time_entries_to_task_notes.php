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
        // First, add missing fields to task_notes table to accommodate time_entries data
        Schema::table('task_notes', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('task_notes', 'description')) {
                $table->text('description')->nullable()->after('content');
            }
            if (!Schema::hasColumn('task_notes', 'start_time')) {
                $table->datetime('start_time')->nullable()->after('total_minutes');
            }
            if (!Schema::hasColumn('task_notes', 'end_time')) {
                $table->datetime('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('task_notes', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('task_notes', 'entry_date')) {
                $table->date('entry_date')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('task_notes', 'is_running')) {
                $table->boolean('is_running')->default(false)->after('entry_date');
            }
            if (!Schema::hasColumn('task_notes', 'source')) {
                $table->string('source')->default('manual')->after('is_running');
            }
        });

        // Migrate all data from time_entries to task_notes
        $timeEntries = DB::table('time_entries')->get();

        foreach ($timeEntries as $timeEntry) {
            // Skip entries with null task_id as they can't be migrated to task_notes
            if ($timeEntry->task_id === null) {
                Log::warning("Skipping time entry with null task_id: ID {$timeEntry->id}");
                continue;
            }

            DB::table('task_notes')->insert([
                'task_id' => $timeEntry->task_id,
                'user_id' => $timeEntry->user_id,
                'content' => $timeEntry->description ?? 'Migrated time entry',
                'description' => $timeEntry->description,
                'hours' => $timeEntry->duration_minutes ? floor($timeEntry->duration_minutes / 60) : null,
                'minutes' => $timeEntry->duration_minutes ? ($timeEntry->duration_minutes % 60) : null,
                'total_minutes' => $timeEntry->duration_minutes,
                'start_time' => $timeEntry->start_time,
                'end_time' => $timeEntry->end_time,
                'duration_minutes' => $timeEntry->duration_minutes,
                'entry_date' => $timeEntry->entry_date,
                'is_running' => $timeEntry->is_running ?? false,
                'source' => 'migrated',
                'created_at' => $timeEntry->created_at,
                'updated_at' => $timeEntry->updated_at,
            ]);
        }

        // Log the migration results
        $migratedCount = DB::table('task_notes')->where('source', 'migrated')->count();
        Log::info("Migrated {$migratedCount} time entries to task_notes table");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated entries
        DB::table('task_notes')->where('source', 'migrated')->delete();

        // Remove added columns (only if they exist and were added by this migration)
        Schema::table('task_notes', function (Blueprint $table) {
            if (Schema::hasColumn('task_notes', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('task_notes', 'is_running')) {
                $table->dropColumn('is_running');
            }
            if (Schema::hasColumn('task_notes', 'entry_date')) {
                $table->dropColumn('entry_date');
            }
            if (Schema::hasColumn('task_notes', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
            if (Schema::hasColumn('task_notes', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('task_notes', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('task_notes', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
