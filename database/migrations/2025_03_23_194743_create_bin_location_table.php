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
        Schema::create('bin_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 20);
            $table->json('position')->nullable()->comment('Position in the warehouse (aisle, bay, level');
            $table->decimal('capacity', 10, 3)->nullable()->comment('Capacity in cubic units');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // create a unique constraint on zone_id and code
            $table->unique(['zone_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bin_location');
    }
};
