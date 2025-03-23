<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user with a new tenant
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required', 
                'string', 
                'max:50', 
                'alpha_dash', 
                'unique:domains,domain'
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Generate a UUID for the tenant
        $tenantId = (string) Str::uuid();
        
        // Create new tenant in the database
        $tenant = Tenant::create([
            'id' => $tenantId,
            'data' => [
                'name' => $request->business_name,
                // For development, we'll use the central database
                'database' => config('database.default')
            ]
        ]);
        
        // Create domain for tenant
        $domain = Domain::create([
            'domain' => $request->subdomain,
            'tenant_id' => $tenantId
        ]);
        
        // Create roles for the new tenant
        $adminRole = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Administrator',
            'slug' => 'administrator',
            'description' => 'Full system access',
            'is_default' => true,
        ]);
        
        // Create the first user with admin privileges
        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'email' => $request->email,
            'job_title' => 'Administrator',
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);
        
        // Assign admin role to user
        $user->roles()->attach($adminRole);
        
        // Create API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'tenant' => $tenant,
            'token' => $token,
        ], 201);
    }
    
    /**
     * Login user and create token
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate login data
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Find user by email
        $user = User::where('email', $request->email)->first();
        
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'The provided credentials are incorrect.'
            ], 401);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'error' => 'Your account has been deactivated.'
            ], 403);
        }
        
        // Get tenant details
        $tenant = $user->tenant;
        
        // Manually set the current tenant to ensure proper tenant scoping
        app()->instance('current_tenant', $tenant);
        
        // Revoke previous tokens (optional - depends on your security requirements)
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Load relationships for the response
        $user->load('roles');
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'tenant' => $tenant,
            'token' => $token,
        ]);
    }
    
    /**
     * Get the authenticated user information
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        // Get the authenticated user with roles and tenant
        $user = $request->user();
        $user->load(['roles.permissions', 'tenant']);
        
        // Manually set the current tenant to ensure proper tenant scoping
        app()->instance('current_tenant', $user->tenant);
        
        return response()->json([
            'user' => $user,
        ]);
    }
    
    /**
     * Logout user (Revoke the token)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}