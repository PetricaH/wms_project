<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the permissions structure
        $permissionsData = [
            // Inventory management permissions
            [
                'name' => 'View Inventory',
                'slug' => 'inventory.view',
                'description' => 'View inventory levels and movements',
            ],
            [
                'name' => 'Receive Inventory',
                'slug' => 'inventory.receive',
                'description' => 'Receive new inventory into the system',
            ],
            [
                'name' => 'Transfer Inventory',
                'slug' => 'inventory.transfer',
                'description' => 'Transfer inventory between locations',
            ],
            [
                'name' => 'Pick Inventory',
                'slug' => 'inventory.pick',
                'description' => 'Pick inventory for orders',
            ],
            [
                'name' => 'Adjust Inventory',
                'slug' => 'inventory.adjust',
                'description' => 'Make inventory adjustments',
            ],
            
            // Product management permissions
            [
                'name' => 'View Products',
                'slug' => 'products.view',
                'description' => 'View product information',
            ],
            [
                'name' => 'Create Products',
                'slug' => 'products.create',
                'description' => 'Create new products',
            ],
            [
                'name' => 'Edit Products',
                'slug' => 'products.edit',
                'description' => 'Edit existing products',
            ],
            [
                'name' => 'Delete Products',
                'slug' => 'products.delete',
                'description' => 'Delete products',
            ],
            
            // Warehouse management permissions
            [
                'name' => 'View Warehouses',
                'slug' => 'warehouses.view',
                'description' => 'View warehouse information',
            ],
            [
                'name' => 'Create Warehouses',
                'slug' => 'warehouses.create',
                'description' => 'Create new warehouses',
            ],
            [
                'name' => 'Edit Warehouses',
                'slug' => 'warehouses.edit',
                'description' => 'Edit existing warehouses',
            ],
            [
                'name' => 'Delete Warehouses',
                'slug' => 'warehouses.delete',
                'description' => 'Delete warehouses',
            ],
            
            // Supplier management permissions
            [
                'name' => 'View Suppliers',
                'slug' => 'suppliers.view',
                'description' => 'View supplier information',
            ],
            [
                'name' => 'Create Suppliers',
                'slug' => 'suppliers.create',
                'description' => 'Create new suppliers',
            ],
            [
                'name' => 'Edit Suppliers',
                'slug' => 'suppliers.edit',
                'description' => 'Edit existing suppliers',
            ],
            [
                'name' => 'Delete Suppliers',
                'slug' => 'suppliers.delete',
                'description' => 'Delete suppliers',
            ],
            
            // Purchase order management permissions
            [
                'name' => 'View Purchase Orders',
                'slug' => 'purchase_orders.view',
                'description' => 'View purchase order information',
            ],
            [
                'name' => 'Create Purchase Orders',
                'slug' => 'purchase_orders.create',
                'description' => 'Create new purchase orders',
            ],
            [
                'name' => 'Edit Purchase Orders',
                'slug' => 'purchase_orders.edit',
                'description' => 'Edit existing purchase orders',
            ],
            [
                'name' => 'Delete Purchase Orders',
                'slug' => 'purchase_orders.delete',
                'description' => 'Delete purchase orders',
            ],
            [
                'name' => 'Approve Purchase Orders',
                'slug' => 'purchase_orders.approve',
                'description' => 'Approve purchase orders',
            ],
            [
                'name' => 'Send Purchase Orders',
                'slug' => 'purchase_orders.send',
                'description' => 'Send purchase orders to suppliers',
            ],
            
            // Receiving permissions
            [
                'name' => 'View Receiving',
                'slug' => 'receiving.view',
                'description' => 'View receiving information',
            ],
            [
                'name' => 'Receive Items',
                'slug' => 'receiving.receive',
                'description' => 'Receive items from purchase orders',
            ],
            [
                'name' => 'Reject Items',
                'slug' => 'receiving.reject',
                'description' => 'Reject items from purchase orders',
            ],
            [
                'name' => 'Close Receiving',
                'slug' => 'receiving.close',
                'description' => 'Close receiving for purchase orders',
            ],
            [
                'name' => 'Perform Quality Inspection',
                'slug' => 'receiving.quality_inspection',
                'description' => 'Perform quality inspection on received items',
            ],
            
            // Order management permissions
            [
                'name' => 'View Orders',
                'slug' => 'orders.view',
                'description' => 'View order information',
            ],
            [
                'name' => 'Create Orders',
                'slug' => 'orders.create',
                'description' => 'Create new orders',
            ],
            [
                'name' => 'Edit Orders',
                'slug' => 'orders.edit',
                'description' => 'Edit existing orders',
            ],
            [
                'name' => 'Delete Orders',
                'slug' => 'orders.delete',
                'description' => 'Delete orders',
            ],
            [
                'name' => 'Process Order Payments',
                'slug' => 'orders.process_payment',
                'description' => 'Process payments for orders',
            ],
            [
                'name' => 'Allocate Inventory',
                'slug' => 'orders.allocate',
                'description' => 'Allocate inventory for orders',
            ],
            [
                'name' => 'Cancel Orders',
                'slug' => 'orders.cancel',
                'description' => 'Cancel orders',
            ],
            
            // Picking permissions
            [
                'name' => 'View Picking',
                'slug' => 'picking.view',
                'description' => 'View picking information',
            ],
            [
                'name' => 'Pick Items',
                'slug' => 'picking.pick',
                'description' => 'Pick items for orders',
            ],
            [
                'name' => 'Complete Picking',
                'slug' => 'picking.complete',
                'description' => 'Complete picking for orders',
            ],
            [
                'name' => 'Wave Picking',
                'slug' => 'picking.wave',
                'description' => 'Generate and process wave picking',
            ],
            
            // Packing permissions
            [
                'name' => 'View Packing',
                'slug' => 'packing.view',
                'description' => 'View packing information',
            ],
            [
                'name' => 'Pack Items',
                'slug' => 'packing.pack_items',
                'description' => 'Pack items for orders',
            ],
            [
                'name' => 'Complete Packing',
                'slug' => 'packing.complete',
                'description' => 'Complete packing for orders',
            ],
            
            // Shipping permissions
            [
                'name' => 'View Shipping',
                'slug' => 'shipping.view',
                'description' => 'View shipping information',
            ],
            [
                'name' => 'Ship Orders',
                'slug' => 'shipping.ship_orders',
                'description' => 'Ship orders',
            ],
            [
                'name' => 'Track Shipments',
                'slug' => 'shipping.track',
                'description' => 'Track shipments',
            ],
        ];
        
        // Create permissions
        foreach ($permissionsData as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }
        
        // Create default roles with permissions
        
        // Admin role - gets all permissions
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full access to all system features',
            ]
        );
        
        // Get all permissions and sync them to the admin role
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id')->toArray());
        
        // Warehouse Manager role
        $warehouseManagerRole = Role::firstOrCreate(
            ['name' => 'warehouse_manager'],
            [
                'display_name' => 'Warehouse Manager',
                'description' => 'Manages warehouse operations',
            ]
        );
        
        // Get warehouse-related permissions
        $warehousePermissionSlugs = [
            'warehouses.view', 'warehouses.edit',
            'inventory.view', 'inventory.receive', 'inventory.transfer', 'inventory.adjust',
            'products.view',
            'suppliers.view',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit',
            'receiving.view', 'receiving.receive', 'receiving.reject', 'receiving.close', 'receiving.quality_inspection',
            'orders.view', 'orders.allocate',
            'picking.view', 'picking.pick', 'picking.complete', 'picking.wave',
            'packing.view', 'packing.pack_items', 'packing.complete',
            'shipping.view', 'shipping.ship_orders', 'shipping.track',
        ];
        
        $warehousePermissions = Permission::whereIn('slug', $warehousePermissionSlugs)->get();
        $warehouseManagerRole->permissions()->sync($warehousePermissions->pluck('id')->toArray());
        
        // Inventory Clerk role
        $inventoryClerkRole = Role::firstOrCreate(
            ['name' => 'inventory_clerk'],
            [
                'display_name' => 'Inventory Clerk',
                'description' => 'Handles inventory operations',
            ]
        );
        
        // Get inventory-related permissions
        $inventoryPermissionSlugs = [
            'inventory.view', 'inventory.receive', 'inventory.transfer',
            'products.view',
            'warehouses.view',
            'receiving.view', 'receiving.receive',
            'picking.view', 'picking.pick',
            'packing.view', 'packing.pack_items',
        ];
        
        $inventoryPermissions = Permission::whereIn('slug', $inventoryPermissionSlugs)->get();
        $inventoryClerkRole->permissions()->sync($inventoryPermissions->pluck('id')->toArray());
        
        // Order Processor role
        $orderProcessorRole = Role::firstOrCreate(
            ['name' => 'order_processor'],
            [
                'display_name' => 'Order Processor',
                'description' => 'Processes customer orders',
            ]
        );
        
        // Get order-related permissions
        $orderPermissionSlugs = [
            'orders.view', 'orders.create', 'orders.edit', 'orders.process_payment',
            'inventory.view',
            'products.view',
            'picking.view',
            'packing.view',
            'shipping.view', 'shipping.track',
        ];
        
        $orderPermissions = Permission::whereIn('slug', $orderPermissionSlugs)->get();
        $orderProcessorRole->permissions()->sync($orderPermissions->pluck('id')->toArray());
    }
}