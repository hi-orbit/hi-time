<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'signed' status
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'cancelled', 'signed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'signed' from the enum
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'cancelled') DEFAULT 'draft'");
    }
};
