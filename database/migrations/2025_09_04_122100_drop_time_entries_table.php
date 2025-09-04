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
        // Drop the time_entries table as all data has been migrated to task_notes
        Schema::dropIfExists('time_entries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the time_entries table structure if needed for rollback
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date')->nullable();
            $table->text('description')->nullable();
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_running')->default(false);
            $table->timestamps();
        });
    }
};
