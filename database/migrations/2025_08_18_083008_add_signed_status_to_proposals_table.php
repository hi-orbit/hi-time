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
        // Update the enum to include 'signed' status.
        // MySQL only - on other drivers (e.g. sqlite in tests) the column is already a plain string.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'cancelled', 'signed') DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'signed' from the enum (MySQL only, see up()).
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'cancelled') DEFAULT 'draft'");
        }
    }
};
