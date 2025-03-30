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
        // Create the order_items table to track line items within customer orders
        Schema::create('order_items', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Link to parent order - if order is deleted, delete all its items
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Link to product - prevent deletion of products that are in orders
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            
            // Item details
            $table->integer('line_number');           // Position in the order
            $table->string('sku');                    // SKU at time of order (in case product SKU changes)
            $table->string('name');                   // Product name at time of order
            $table->text('description')->nullable();  // Product description at time of order
            $table->decimal('quantity', 12, 3);       // Quantity ordered
            $table->string('unit_of_measure')->default('ea');  // Unit of measure (each, kg, etc.)
            
            // Pricing information
            $table->decimal('unit_price', 12, 4);     // Price per unit
            $table->decimal('tax_rate', 6, 4)->default(0);  // Tax rate (percentage)
            $table->decimal('tax_amount', 12, 2);     // Tax amount
            $table->decimal('discount_percentage', 6, 4)->default(0);  // Discount percentage
            $table->decimal('discount_amount', 12, 2)->default(0);  // Discount amount
            $table->decimal('subtotal', 12, 2);       // Subtotal (before tax)
            $table->decimal('total', 12, 2);          // Total (including tax)
            
            // Fulfillment information
            $table->decimal('quantity_allocated', 12, 3)->default(0);  // Quantity allocated from inventory
            $table->decimal('quantity_picked', 12, 3)->default(0);     // Quantity picked
            $table->decimal('quantity_shipped', 12, 3)->default(0);    // Quantity shipped
            $table->decimal('quantity_returned', 12, 3)->default(0);   // Quantity returned
            
            // Status information
            $table->enum('status', [
                'pending',       // Not yet processed
                'allocated',     // Inventory has been allocated
                'partially_allocated',  // Some inventory has been allocated
                'picking',       // Currently being picked
                'picked',        // Has been picked
                'packed',        // Has been packed
                'shipped',       // Has been shipped
                'backordered',   // Not enough inventory
                'cancelled'      // Item was cancelled
            ])->default('pending');
            
            // Lot tracking
            $table->string('lot_number')->nullable();  // Lot number (if applicable)
            $table->string('serial_number')->nullable();  // Serial number (if applicable)
            $table->date('expiry_date')->nullable();   // Expiration date (if applicable)
            
            // Fulfillment details
            $table->json('allocation_details')->nullable();  // Details about inventory allocation
            $table->json('picking_details')->nullable();     // Details about picking
            $table->json('packing_details')->nullable();     // Details about packing
            
            // Additional information
            $table->text('notes')->nullable();        // Notes specific to this item
            $table->json('metadata')->nullable();     // Additional metadata
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'product_id']);
            $table->index('status');
            
            // Ensure each product can only appear once in an order (with different line numbers)
            $table->unique(['order_id', 'line_number']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};