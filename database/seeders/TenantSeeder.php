<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default tenant for testing
        $tenantId = (string) Str::uuid();
        
        // Get current database name for development
        $currentDatabase = Config::get('database.connections.' . Config::get('database.default') . '.database');
        
        // Insert tenant - using the current database for development
        DB::table('tenants')->insert([
            'id' => $tenantId,
            'data' => json_encode([
                'name' => 'Demo Company',
                'database' => $currentDatabase
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create domain for the tenant
        DB::table('domains')->insert([
            'domain' => 'demo',
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create admin role
        $adminRoleId = DB::table('roles')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Administrator',
            'slug' => 'administrator',
            'description' => 'Full system access',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create standard roles
        $managerRoleId = DB::table('roles')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Warehouse Manager',
            'slug' => 'warehouse-manager',
            'description' => 'Manages warehouse operations',
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $clerkRoleId = DB::table('roles')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Inventory Clerk',
            'slug' => 'inventory-clerk',
            'description' => 'Handles inventory tasks',
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create permissions
        $permissions = [
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view user list and details'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'description' => 'Can edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Can delete users'],
            ['name' => 'View Products', 'slug' => 'view-products', 'description' => 'Can view product list and details'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'description' => 'Can create new products'],
            ['name' => 'Edit Products', 'slug' => 'edit-products', 'description' => 'Can edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'description' => 'Can delete products'],
            ['name' => 'View Inventory', 'slug' => 'view-inventory', 'description' => 'Can view inventory levels'],
            ['name' => 'Adjust Inventory', 'slug' => 'adjust-inventory', 'description' => 'Can adjust inventory levels'],
            ['name' => 'Move Inventory', 'slug' => 'move-inventory', 'description' => 'Can move inventory between locations'],
        ];
        
        $permissionIds = [];
        
        foreach ($permissions as $permission) {
            $permissionIds[$permission['slug']] = DB::table('permissions')->insertGetId([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Assign all permissions to admin role
        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
            ]);
        }
        
        // Assign selected permissions to manager role
        $managerPermissions = ['view-users', 'view-products', 'edit-products', 'view-inventory', 'adjust-inventory', 'move-inventory'];
        foreach ($managerPermissions as $permissionSlug) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionIds[$permissionSlug],
                'role_id' => $managerRoleId,
            ]);
        }
        
        // Assign selected permissions to clerk role
        $clerkPermissions = ['view-products', 'view-inventory', 'adjust-inventory'];
        foreach ($clerkPermissions as $permissionSlug) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionIds[$permissionSlug],
                'role_id' => $clerkRoleId,
            ]);
        }
        
        // Create admin user
        $adminUserId = DB::table('users')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'job_title' => 'System Administrator',
            'password' => Hash::make('password'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create manager user
        $managerUserId = DB::table('users')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Warehouse Manager',
            'email' => 'manager@example.com',
            'job_title' => 'Warehouse Manager',
            'password' => Hash::make('password'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create clerk user
        $clerkUserId = DB::table('users')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Inventory Clerk',
            'email' => 'clerk@example.com',
            'job_title' => 'Inventory Clerk',
            'password' => Hash::make('password'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Assign roles to users
        DB::table('role_user')->insert([
            ['role_id' => $adminRoleId, 'user_id' => $adminUserId],
            ['role_id' => $managerRoleId, 'user_id' => $managerUserId],
            ['role_id' => $clerkRoleId, 'user_id' => $clerkUserId],
        ]);
    }
}