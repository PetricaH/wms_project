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
        // Create the orders table to track customer orders
        Schema::create('orders', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Order identifiers
            $table->string('order_number')->unique();  // Unique order number for reference
            $table->string('external_order_id')->nullable();  // ID from external system (e.g., e-commerce platform)
            
            // Customer information
            $table->string('customer_name');           // Name of the customer
            $table->string('customer_email')->nullable();  // Email address for contact
            $table->string('customer_phone')->nullable();  // Phone number for contact
            
            // Addresses
            $table->text('shipping_address');          // Where to ship the order
            $table->text('billing_address')->nullable();  // Billing address (if different from shipping)
            
            // Order details
            $table->foreignId('warehouse_id')->constrained();  // Which warehouse will fulfill this order
            $table->date('order_date');               // When the order was placed
            $table->date('due_date')->nullable();     // When the order should be shipped by
            
            // Financial information
            $table->string('currency', 3)->default('USD');  // Currency code
            $table->decimal('subtotal', 12, 2);       // Subtotal before tax and shipping
            $table->decimal('tax_amount', 12, 2);     // Tax amount
            $table->decimal('shipping_amount', 12, 2);  // Shipping cost
            $table->decimal('discount_amount', 12, 2)->default(0);  // Discount amount
            $table->decimal('total_amount', 12, 2);   // Total amount after all adjustments
            
            // Status information
            $table->enum('status', [
                'pending',          // New order, not yet processed
                'processing',       // Order is being processed
                'awaiting_payment', // Waiting for payment confirmation
                'paid',             // Payment received
                'ready_to_pick',    // Ready for warehouse picking
                'picking',          // Currently being picked
                'picked',           // All items picked
                'packing',          // Currently being packed
                'packed',           // All items packed
                'awaiting_shipment', // Ready for shipment
                'shipped',          // Order has been shipped
                'delivered',        // Order has been delivered
                'cancelled',        // Order was cancelled
                'returned',         // Order was returned
                'completed',        // Order is fully completed
                'on_hold'           // Order is on hold
            ])->default('pending');
            
            // Payment information
            $table->enum('payment_status', [
                'pending',           // Payment not yet received
                'authorized',        // Payment authorized but not captured
                'paid',              // Payment received
                'partially_refunded', // Partial refund issued
                'fully_refunded',    // Full refund issued
                'failed'             // Payment failed
            ])->default('pending');
            $table->string('payment_method')->nullable();  // Method of payment
            $table->string('payment_reference')->nullable();  // Reference ID for payment
            
            // Shipping information
            $table->string('shipping_method')->nullable();  // Method of shipping
            $table->string('tracking_number')->nullable();  // Tracking number for shipment
            $table->string('carrier')->nullable();          // Shipping carrier
            $table->date('shipped_date')->nullable();       // When the order was shipped
            $table->date('estimated_delivery_date')->nullable();  // Estimated delivery date
            $table->date('actual_delivery_date')->nullable();  // When the order was actually delivered
            
            // Processing information
            $table->foreignId('assigned_to')->nullable()->constrained('users');  // User assigned to process this order
            $table->timestamp('picked_at')->nullable();     // When the order was picked
            $table->foreignId('picked_by')->nullable()->constrained('users');  // User who picked the order
            $table->timestamp('packed_at')->nullable();     // When the order was packed
            $table->foreignId('packed_by')->nullable()->constrained('users');  // User who packed the order
            $table->timestamp('cancelled_at')->nullable();  // When the order was cancelled
            $table->foreignId('cancelled_by')->nullable()->constrained('users');  // User who cancelled the order
            $table->text('cancellation_reason')->nullable();  // Reason for cancellation
            
            // Additional information
            $table->string('customer_reference')->nullable();  // Customer's reference (e.g., PO number)
            $table->text('customer_notes')->nullable();        // Notes from the customer
            $table->text('internal_notes')->nullable();        // Internal notes about the order
            $table->string('source')->nullable();              // Source of the order (e.g., website, phone)
            $table->json('metadata')->nullable();              // Additional metadata about the order
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();  // Allows for "trash" functionality
            
            // Indexes for frequently queried columns
            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('order_date');
            $table->index('due_date');
            $table->index('shipped_date');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};