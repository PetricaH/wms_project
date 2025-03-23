<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define all system permissions
        $permissions = [
            // User management
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view user list and details'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'description' => 'Can edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Can delete users'],
            
            // Product management
            ['name' => 'View Products', 'slug' => 'view-products', 'description' => 'Can view product list and details'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'description' => 'Can create new products'],
            ['name' => 'Edit Products', 'slug' => 'edit-products', 'description' => 'Can edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'description' => 'Can delete products'],
            
            // Inventory management
            ['name' => 'View Inventory', 'slug' => 'view-inventory', 'description' => 'Can view inventory levels'],
            ['name' => 'Adjust Inventory', 'slug' => 'adjust-inventory', 'description' => 'Can adjust inventory levels'],
            ['name' => 'Move Inventory', 'slug' => 'move-inventory', 'description' => 'Can move inventory between locations'],
            
            // Warehouse operations
            ['name' => 'Receive Items', 'slug' => 'receive-items', 'description' => 'Can receive items into the warehouse'],
            ['name' => 'Pick Items', 'slug' => 'pick-items', 'description' => 'Can pick items for orders'],
            ['name' => 'Pack Items', 'slug' => 'pack-items', 'description' => 'Can pack items for shipping'],
            ['name' => 'Ship Items', 'slug' => 'ship-items', 'description' => 'Can mark items as shipped'],
            
            // Reporting
            ['name' => 'View Reports', 'slug' => 'view-reports', 'description' => 'Can view system reports'],
            ['name' => 'Export Data', 'slug' => 'export-data', 'description' => 'Can export data from the system'],
        ];
        
        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        // Assign permissions to roles based on role type
        // For each business's roles
        $businesses = \App\Models\Business::all();
        
        foreach ($businesses as $business) {
            // Get roles for this business
            $administratorRole = Role::where('business_id', $business->id)
                ->where('slug', 'administrator')
                ->first();
                
            $warehouseManagerRole = Role::where('business_id', $business->id)
                ->where('slug', 'warehouse-manager')
                ->first();
                
            $inventoryClerkRole = Role::where('business_id', $business->id)
                ->where('slug', 'inventory-clerk')
                ->first();
                
            $shippingCoordinatorRole = Role::where('business_id', $business->id)
                ->where('slug', 'shipping-coordinator')
                ->first();
            
            // Admin gets all permissions
            if ($administratorRole) {
                $administratorRole->permissions()->attach(Permission::all());
            }
            
            // Warehouse manager permissions
            if ($warehouseManagerRole) {
                $warehouseManagerRole->permissions()->attach(
                    Permission::whereIn('slug', [
                        'view-users', 
                        'view-products', 'edit-products', 
                        'view-inventory', 'adjust-inventory', 'move-inventory',
                        'receive-items', 'pick-items', 'pack-items', 'ship-items',
                        'view-reports'
                    ])->get()
                );
            }
            
            // Inventory clerk permissions
            if ($inventoryClerkRole) {
                $inventoryClerkRole->permissions()->attach(
                    Permission::whereIn('slug', [
                        'view-products',
                        'view-inventory', 'adjust-inventory', 'move-inventory',
                        'receive-items'
                    ])->get()
                );
            }
            
            // Shipping coordinator permissions
            if ($shippingCoordinatorRole) {
                $shippingCoordinatorRole->permissions()->attach(
                    Permission::whereIn('slug', [
                        'view-products',
                        'view-inventory',
                        'pick-items', 'pack-items', 'ship-items'
                    ])->get()
                );
            }
        }
    }
}