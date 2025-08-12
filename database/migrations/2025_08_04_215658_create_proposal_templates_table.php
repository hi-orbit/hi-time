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
        Schema::create('proposal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // service, product, consulting, etc.
            $table->text('description')->nullable();
            $table->longText('content'); // HTML content from WYSIWYG editor
            $table->json('variables')->nullable(); // Template variables like {{company_name}}, {{amount}}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_templates');
    }
};
