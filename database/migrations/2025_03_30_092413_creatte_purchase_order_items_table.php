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
        // Create the purchase_order_items table to track line items within purchase orders
        Schema::create('purchase_order_items', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Relationship to purchase_orders table
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            // ^ Links to purchase_orders table and will be deleted if the parent PO is deleted
            
            // Relationship to products table
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            // ^ Links to products table and prevents deletion of products with pending PO items
            
            // Item details
            $table->integer('line_number')->default(1);  // Line number within the PO
            $table->decimal('quantity_ordered', 12, 3);  // Quantity ordered
            $table->decimal('quantity_received', 12, 3)->default(0);  // Quantity received so far
            $table->decimal('quantity_rejected', 12, 3)->default(0);  // Quantity rejected during receiving
            $table->string('unit_of_measure')->default('ea');  // Unit of measure (each, kg, etc.)
            
            // Pricing information
            $table->decimal('unit_price', 12, 4);  // Price per unit
            $table->decimal('tax_rate', 6, 4)->default(0);  // Tax rate (percentage)
            $table->decimal('tax_amount', 12, 2)->default(0);  // Tax amount
            $table->decimal('discount_percentage', 6, 4)->default(0);  // Discount percentage
            $table->decimal('discount_amount', 12, 2)->default(0);  // Discount amount
            $table->decimal('subtotal', 12, 2);  // Subtotal (before tax)
            $table->decimal('total', 12, 2);  // Total (after tax)
            
            // Expected receiving information
            $table->foreignId('destination_location_id')->nullable()->constrained('bin_locations');
            // ^ Where the items should be stored upon receipt
            
            // Status information
            $table->enum('status', [
                'pending',             // Waiting to be received
                'partially_received',  // Some quantity received
                'fully_received',      // All quantity received
                'over_received',       // More than ordered quantity received
                'cancelled'            // Item cancelled
            ])->default('pending');
            
            // Additional information
            $table->string('supplier_product_code')->nullable();  // Supplier's product code
            $table->text('notes')->nullable();  // Additional notes specific to this item
            
            // Receiving audit fields
            $table->date('expected_delivery_date')->nullable();  // Expected delivery date for this item specifically
            $table->date('last_received_date')->nullable();  // Date of last receipt
            $table->json('receiving_history')->nullable();  // JSON log of receiving transactions
            
            // Standard timestamps
            $table->timestamps();
            
            // Indexes for queries
            $table->index(['purchase_order_id', 'product_id']);
            $table->index('status');
            
            // Ensure line numbers are unique within a purchase order
            $table->unique(['purchase_order_id', 'line_number']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};