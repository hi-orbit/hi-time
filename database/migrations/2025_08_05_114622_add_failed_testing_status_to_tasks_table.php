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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the old enum column and recreate with new values
            $table->dropColumn('status');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['backlog', 'in_progress', 'in_test', 'failed_testing', 'ready_to_release', 'done'])->default('backlog')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert back to original enum values
            $table->dropColumn('status');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['backlog', 'in_progress', 'in_test', 'ready_to_release', 'done'])->default('backlog')->after('description');
        });
    }
};
