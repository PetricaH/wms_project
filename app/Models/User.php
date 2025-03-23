<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id', // Changed from business_id to tenant_id
        'name',
        'email',
        'job_title',
        'phone',
        'password',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the tenant that the user belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    /**
     * Get the roles assigned to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    /**
     * Check if the user has a specific role.
     *
     * @param string|object $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }
        
        return !! $role->intersect($this->roles)->count();
    }
    
    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        // Get all permissions from all the user's roles
        $permissions = $this->roles->map(function($role) {
            return $role->permissions;
        })->flatten();
        
        // Check if the permission exists in the user's permissions
        return $permissions->contains('slug', $permission);
    }
    
    /**
     * Apply tenant scope to all queries
     * This is a global scope that will be applied to all queries
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($query) {
            // Skip the tenant filtering if no current tenant
            if (!app()->has('current_tenant')) {
                return;
            }
            
            $tenant = app('current_tenant');
            $query->where('tenant_id', $tenant->id);
        });
    }
}