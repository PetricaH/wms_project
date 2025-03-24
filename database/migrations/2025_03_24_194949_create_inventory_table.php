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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('bin_locations')->cascadeOnDelete();
            $table->string('lot_number', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('reserved_quantity', 15, 3)->default(0);
            $table->decimal('available_quantity', 15, 3)->default(0);
            $table->string('unit_of_measure', 50)->default('EA');
            $table->date('expiry_date')->nullable();
            $table->date('received_date')->nullable();
            $table->timestamps();

            // create unique constraint to prevent duplicate inventory entries
            $table->unique(['product_id', 'location_id', 'lot_number', 'batch_number'], 'unique_inventory_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
