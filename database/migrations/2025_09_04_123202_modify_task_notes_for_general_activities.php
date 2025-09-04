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
            // Make task_id nullable to support general activities
            $table->foreignId('task_id')->nullable()->change();
            
            // Add activity_type field for general activities
            if (!Schema::hasColumn('task_notes', 'activity_type')) {
                $table->string('activity_type')->nullable()->after('task_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_notes', function (Blueprint $table) {
            // Make task_id required again
            $table->foreignId('task_id')->nullable(false)->change();
            
            // Remove activity_type field
            if (Schema::hasColumn('task_notes', 'activity_type')) {
                $table->dropColumn('activity_type');
            }
        });
    }
};
