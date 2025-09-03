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
            $table->integer('hours')->nullable()->after('content');
            $table->integer('minutes')->nullable()->after('hours');
            $table->integer('total_minutes')->nullable()->after('minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_notes', function (Blueprint $table) {
            $table->dropColumn(['hours', 'minutes', 'total_minutes']);
        });
    }
};
