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
        // Check if the unique constraint exists before trying to drop it
        // (driver-agnostic; the previous `SHOW INDEX` query is MySQL-only).
        $hasNameUnique = Schema::hasIndex('tags', 'tags_name_unique');

        Schema::table('tags', function (Blueprint $table) use ($hasNameUnique) {
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade')->after('name');

            if ($hasNameUnique) {
                $table->dropUnique(['name']);
            }

            // Add composite unique constraint for name + customer_id
            $table->unique(['name', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['name', 'customer_id']);

            // Drop the customer_id column
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            // Restore the original unique constraint on name
            $table->unique('name');
        });

        // Note: The deleted tags and task_tag relationships cannot be restored
        // This is an irreversible data change
    }
};
