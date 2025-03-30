<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Create the suppliers table to store information about all suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            // Primary key - auto-incrementing ID
            $table->id();
            
            // Basic supplier information
            $table->string('name');                 // Company name
            $table->string('code')->unique();       // Unique supplier code/identifier for quick reference
            $table->string('contact_name')->nullable();  // Primary contact person
            $table->string('email')->nullable();    // Contact email
            $table->string('phone')->nullable();    // Contact phone number
            
            // Address information
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            
            // Business information
            $table->string('tax_id')->nullable();   // Tax identification number
            $table->string('website')->nullable();  // Supplier website
            
            // Account information
            $table->string('account_number')->nullable();  // Your account number with this supplier
            $table->string('payment_terms')->nullable();   // Net 30, etc.
            $table->string('currency', 3)->default('USD'); // Default currency for this supplier
            
            // Performance metrics (can be updated periodically based on transactions)
            $table->integer('lead_time_days')->nullable();  // Average lead time in days
            $table->decimal('quality_rating', 3, 1)->nullable(); // Quality rating (e.g., 0-5)
            $table->decimal('on_time_delivery_rate', 5, 2)->nullable(); // Percentage of on-time deliveries
            
            // Status
            $table->boolean('is_active')->default(true);  // Whether this supplier is active
            $table->text('notes')->nullable();           // Additional notes about this supplier
            
            // Timestamps for record-keeping
            $table->timestamps();  // Creates created_at and updated_at columns
            
            // Add indexes for frequently queried columns
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Drop the suppliers table when rolling back the migration
        Schema::dropIfExists('suppliers');
    }
};