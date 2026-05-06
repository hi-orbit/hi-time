<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'general' while preserving existing task statuses.
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('backlog', 'in_progress', 'in_test', 'failed_testing', 'ready_to_release', 'done', 'general') NOT NULL DEFAULT 'backlog'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map 'general' back to 'backlog' before removing the enum value.
        DB::table('tasks')->where('status', 'general')->update(['status' => 'backlog']);

        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('backlog', 'in_progress', 'in_test', 'failed_testing', 'ready_to_release', 'done') NOT NULL DEFAULT 'backlog'");
    }
};
