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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // Lead or Customer relationship
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');

            // Template and content
            $table->foreignId('template_id')->nullable()->constrained('proposal_templates')->onDelete('set null');
            $table->longText('content'); // Final HTML content

            // Financial details
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('terms')->nullable();

            // Status and tracking
            $table->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'cancelled'])->default('draft');
            $table->string('recipient_email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            // E-signature
            $table->string('signature_token')->nullable();
            $table->json('signature_data')->nullable(); // Store signature details

            // Email tracking
            $table->text('email_subject')->nullable();
            $table->text('email_body')->nullable();

            // PDF generation
            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Ensure either lead_id or customer_id is set, but not both
            $table->index(['lead_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
