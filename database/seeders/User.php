<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all businesses to create users for each one
        $businesses = Business::all();
        
        foreach ($businesses as $business) {
            // Create admin user for each business
            $adminUser = User::create([
                'business_id' => $business->id,
                'name' => 'Admin User',
                'email' => 'admin@' . $business->subdomain . '.example.com',
                'job_title' => 'System Administrator',
                'phone' => '555-123-4567',
                'password' => Hash::make('password'), // In production, use a secure password
                'is_active' => true,
            ]);
            
            // Assign admin role
            $adminRole = Role::where('business_id', $business->id)
                ->where('slug', 'administrator')
                ->first();
                
            if ($adminRole) {
                $adminUser->roles()->attach($adminRole);
            }
            
            // Create warehouse manager
            $managerUser = User::create([
                'business_id' => $business->id,
                'name' => 'Warehouse Manager',
                'email' => 'manager@' . $business->subdomain . '.example.com',
                'job_title' => 'Warehouse Manager',
                'phone' => '555-123-4568',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            
            $managerRole = Role::where('business_id', $business->id)
                ->where('slug', 'warehouse-manager')
                ->first();
                
            if ($managerRole) {
                $managerUser->roles()->attach($managerRole);
            }
            
            // Create inventory clerk
            $clerkUser = User::create([
                'business_id' => $business->id,
                'name' => 'Inventory Clerk',
                'email' => 'clerk@' . $business->subdomain . '.example.com',
                'job_title' => 'Inventory Clerk',
                'phone' => '555-123-4569',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            
            $clerkRole = Role::where('business_id', $business->id)
                ->where('slug', 'inventory-clerk')
                ->first();
                
            if ($clerkRole) {
                $clerkUser->roles()->attach($clerkRole);
            }
            
            // Create shipping coordinator
            $shippingUser = User::create([
                'business_id' => $business->id,
                'name' => 'Shipping Coordinator',
                'email' => 'shipping@' . $business->subdomain . '.example.com',
                'job_title' => 'Shipping Coordinator',
                'phone' => '555-123-4570',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            
            $shippingRole = Role::where('business_id', $business->id)
                ->where('slug', 'shipping-coordinator')
                ->first();
                
            if ($shippingRole) {
                $shippingUser->roles()->attach($shippingRole);
            }
        }
    }
}