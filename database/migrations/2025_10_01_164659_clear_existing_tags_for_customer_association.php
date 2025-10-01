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
        Log::info('Clearing existing tags to enforce customer association...');

        // Clear all existing tags and their relationships
        // This ensures that all future tags will be properly associated with customers
        DB::table('task_tag')->delete();
        DB::table('tags')->delete();

        Log::info('All existing tags and tag relationships have been cleared.');
        Log::info('New tags created will be properly associated with customers.');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it deletes data
        Log::warning('Cannot reverse the clearing of tags - this is an irreversible data operation.');
    }
};
