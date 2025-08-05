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
        Schema::table('time_entries', function (Blueprint $table) {
            // Make task_id nullable to support non-task activities
            $table->foreignId('task_id')->nullable()->change();

            // Add activity_type for standard duties
            $table->string('activity_type')->nullable()->after('task_id');

            // Add project_id for non-task activities that still belong to a project
            $table->foreignId('project_id')->nullable()->after('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['activity_type', 'project_id']);
            $table->foreignId('task_id')->nullable(false)->change();
        });
    }
};
