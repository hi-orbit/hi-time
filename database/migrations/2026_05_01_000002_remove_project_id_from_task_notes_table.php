<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_notes', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            if (Schema::hasColumn('task_notes', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_notes', function (Blueprint $table) {
            // Re-add project_id if needed (for rollback)
            $table->foreignId('project_id')->nullable()->after('task_id')->constrained()->nullOnDelete();
        });
    }
};
