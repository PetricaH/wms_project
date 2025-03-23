<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a default demo business for testing
        Business::create([
            'name' => 'Demo Company',
            'subdomain' => 'demo',
            'database_name' => 'tenant_demo',
            'is_active' => true,
            'settings' => [
                'timezone' => 'UTC',
                'currency' => 'USD'
            ]
        ]);
        
        // Create another test business
        Business::create([
            'name' => 'Test Warehouse Inc.',
            'subdomain' => 'test',
            'database_name' => 'tenant_test',
            'is_active' => true,
            'settings' => [
                'timezone' => 'America/New_York',
                'currency' => 'USD'
            ]
        ]);
    }
}