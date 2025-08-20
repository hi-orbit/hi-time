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
        // Add 'customer' to the user role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'contractor', 'customer') DEFAULT 'user'");

        // Create project_users pivot table for customer assignments
        Schema::create('project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['project_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the project_users table
        Schema::dropIfExists('project_users');

        // Revert the user role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'contractor') DEFAULT 'user'");
    }
};
