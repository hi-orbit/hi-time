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
        // First update any existing data to the new format
        DB::table('leads')->where('status', 'active')->update(['status' => 'new']);
        DB::table('leads')->where('status', 'converted')->update(['status' => 'closed_won']);
        DB::table('leads')->where('status', 'lost')->update(['status' => 'closed_lost']);

        // Use raw SQL to modify the enum column (Laravel's change() doesn't work well with enums)
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'qualified', 'proposal_sent', 'closed_won', 'closed_lost') NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any existing data back to the old format
        DB::table('leads')->where('status', 'new')->update(['status' => 'active']);
        DB::table('leads')->whereIn('status', ['contacted', 'qualified', 'proposal_sent'])->update(['status' => 'active']);
        DB::table('leads')->where('status', 'closed_won')->update(['status' => 'converted']);
        DB::table('leads')->where('status', 'closed_lost')->update(['status' => 'lost']);

        // Revert the enum column using raw SQL
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('active', 'converted', 'lost') NOT NULL DEFAULT 'active'");
    }
};
