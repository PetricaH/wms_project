<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Business;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all businesses to create roles for each one
        $businesses = Business::all();
        
        foreach ($businesses as $business) {
            // For each business, create default roles
            
            // Administrator - has full access to the system
            Role::create([
                'business_id' => $business->id,
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access',
                'is_default' => true,
            ]);
            
            // Warehouse Manager - manages warehouse operations
            Role::create([
                'business_id' => $business->id,
                'name' => 'Warehouse Manager',
                'slug' => 'warehouse-manager',
                'description' => 'Manages warehouse operations',
                'is_default' => false,
            ]);
            
            // Inventory Clerk - handles inventory tasks
            Role::create([
                'business_id' => $business->id,
                'name' => 'Inventory Clerk',
                'slug' => 'inventory-clerk',
                'description' => 'Handles inventory tasks',
                'is_default' => false,
            ]);
            
            // Shipping Coordinator - manages shipping operations
            Role::create([
                'business_id' => $business->id,
                'name' => 'Shipping Coordinator',
                'slug' => 'shipping-coordinator',
                'description' => 'Coordinates shipping operations',
                'is_default' => false,
            ]);
        }
    }
}