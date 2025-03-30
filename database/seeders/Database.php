<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create seeders in the correct order to handle dependencies
        $this->call([
            BusinessSeeder::class,    // First create the businesses
            RoleSeeder::class,        // Then create roles for those businesses
            PermissionsSeeder::class,  // Create permissions and assign to roles
            UserSeeder::class,        // Finally create users and assign to roles
        ]);
    }
}