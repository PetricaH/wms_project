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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type', 100)->nullable()->comment('Reference type (order, transfer, etc.)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Reference ID (order ID, transfer ID, etc.)');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('bin_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('bin_locations')->nullOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->string('unit_of_measure', 50)->default('EA');
            $table->string('lot_number', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->string('movement_type', 50)->comment('receive, transfer, pick, adjust, return, etc.');
            $table->json('fifo_layers')->nullable()->comment('FIFO inventory layers affected');
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            
            // Create index on reference
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};