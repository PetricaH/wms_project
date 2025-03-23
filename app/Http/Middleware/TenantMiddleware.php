<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // For development purposes, we'll use a simplified approach during initial setup
        if (app()->environment('local')) {
            // Just use the first tenant in the system for now
            $tenant = Tenant::first();
            
            if (!$tenant) {
                Log::error('No tenants found in the database. Please run migrations and seeders.');
                return response()->json([
                    'error' => 'No tenants found in the system. Please run migrations and seeders.',
                    'setup_instructions' => 'Run: php artisan db:seed --class=TenantSeeder'
                ], 404);
            }
            
            // Store the current tenant in the app container
            app()->instance('current_tenant', $tenant);
            
            // Configure tenant database connection
            $this->configureTenantConnection($tenant);
            
            return $next($request);
        }
        
        // Production tenant resolution logic
        $tenantId = $this->resolveTenant($request);
        
        if (!$tenantId) {
            Log::warning('Tenant not found for request: ' . $request->fullUrl());
            Log::warning('Host: ' . $request->getHost());
            Log::warning('Headers: ' . json_encode($request->headers->all()));
            
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            Log::error('Tenant ID found but not in database: ' . $tenantId);
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        // Store the current tenant in the app container
        app()->instance('current_tenant', $tenant);
        
        // Configure tenant database connection
        $this->configureTenantConnection($tenant);
        
        return $next($request);
    }
    
    /**
     * Determine which tenant the request belongs to
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function resolveTenant(Request $request)
    {
        // First check for API header
        if ($request->hasHeader('X-Tenant')) {
            $tenantId = $request->header('X-Tenant');
            // Verify that this tenant exists
            if (Tenant::find($tenantId)) {
                return $tenantId;
            }
        }
        
        // Then check for subdomain
        $host = $request->getHost();
        $segments = explode('.', $host);
        
        // For subdomains like demo.example.com
        if (count($segments) > 1) {
            $subdomain = $segments[0];
            
            // Find tenant by domain/subdomain
            $domain = Domain::where('domain', $subdomain)->first();
            if ($domain) {
                return $domain->tenant_id;
            }
        }
        
        // If user is authenticated, use their tenant
        if ($request->user()) {
            return $request->user()->tenant_id;
        }
        
        // Try to get the first tenant in the system as a fallback
        $firstTenant = Tenant::first();
        return $firstTenant ? $firstTenant->id : null;
    }
    
    /**
     * Configure the tenant database connection
     *
     * @param  \App\Models\Tenant  $tenant
     * @return void
     */
    protected function configureTenantConnection($tenant)
    {
        // Get the tenant data JSON
        $tenantData = $tenant->data ?? [];
        
        // Get the base tenant connection config
        $config = Config::get('database.connections.tenant');
        
        // If no tenant connection is defined, use the default connection as a base
        if (!$config) {
            $config = Config::get('database.connections.' . Config::get('database.default'));
        }
        
        // Use database name from tenant data if available
        if (isset($tenantData['database'])) {
            $config['database'] = $tenantData['database'];
        } 
        // Or use a standard naming convention as fallback
        else {
            $config['database'] = 'tenant_' . $tenant->id;
        }
        
        // Set the updated configuration
        Config::set('database.connections.tenant', $config);
        
        // Purge the existing tenant connection if it exists
        DB::purge('tenant');
        
        // Set the default connection to 'tenant'
        DB::setDefaultConnection('tenant');
    }
}