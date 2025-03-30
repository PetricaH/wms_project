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
        // Create the purchase_orders table to track orders made to suppliers
        Schema::create('purchase_orders', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Core information
            $table->string('po_number')->unique();  // Purchase order number - must be unique
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');  
            // ^ Links to suppliers table and prevents deletion of suppliers with POs
            
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            // ^ Specifies which warehouse this PO is for
            
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            // ^ Tracks which user created this PO
            
            // Order dates
            $table->date('order_date');             // When the order was placed
            $table->date('expected_delivery_date')->nullable();  // Expected delivery date
            $table->date('received_date')->nullable();  // When the order was actually received
            
            // Financial information
            $table->string('currency', 3)->default('USD');  // Currency of the order
            $table->decimal('subtotal', 12, 2)->default(0);  // Subtotal before tax
            $table->decimal('tax_amount', 12, 2)->default(0);  // Tax amount
            $table->decimal('shipping_cost', 12, 2)->default(0);  // Shipping cost
            $table->decimal('total_amount', 12, 2)->default(0);  // Total amount
            
            // Status information
            $table->enum('status', [
                'draft',           // Initial state - being prepared
                'awaiting_approval', // Needs approval before sending
                'approved',        // Approved but not yet sent to supplier
                'sent',            // Sent to supplier
                'confirmed',       // Confirmed by supplier
                'partially_received',  // Some items received
                'fully_received',  // All items received
                'closed',          // Order completed and closed
                'cancelled'        // Order was cancelled
            ])->default('draft');
            
            // Additional information
            $table->string('supplier_reference')->nullable();  // Supplier's reference number
            $table->text('shipping_address')->nullable();  // Shipping address (if different from warehouse)
            $table->text('notes')->nullable();  // Additional notes
            $table->text('payment_terms')->nullable();  // Payment terms for this specific order
            
            // For partial receipts
            $table->boolean('allows_partial_receiving')->default(true);  // Whether partial receipts are allowed
            
            // Tracking fields
            $table->timestamp('approved_at')->nullable();  // When order was approved
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sent_at')->nullable();  // When order was sent to supplier
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();  // When order was cancelled
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();  // Reason for cancellation
            
            // Standard timestamps
            $table->timestamps();
            
            // Common indexes
            $table->index('status');
            $table->index('order_date');
            $table->index('expected_delivery_date');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};