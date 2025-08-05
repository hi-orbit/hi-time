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
            // Make task_id nullable to support general activities
            $table->unsignedBigInteger('task_id')->nullable()->change();

            // Add activity_type field for general activities
            $table->string('activity_type')->nullable()->after('task_id');

            // Add project_id to associate general activities with projects
            $table->unsignedBigInteger('project_id')->nullable()->after('activity_type');

            // Add foreign key for project_id
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            // Drop foreign key and columns
            $table->dropForeign(['project_id']);
            $table->dropColumn(['activity_type', 'project_id']);

            // Make task_id required again
            $table->unsignedBigInteger('task_id')->nullable(false)->change();
        });
    }
};
