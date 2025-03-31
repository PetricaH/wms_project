<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'tenant_id', // <-- ADD THIS LINE
        'name',
        'email',
        'password',
        'job_title', // Add other fields you want to be fillable
        'phone',
        'is_active',
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
        'password' => 'hashed', // Use 'hashed' for Laravel 10+
        'is_active' => 'boolean',
    ];


    /**
     * Get the tenant that the user belongs to.
     */
    public function tenant()
    {
        // Ensure this relationship is correctly defined
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the roles assigned to this user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has a specific role
     * 
     * @param string|Role $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        return $this->roles->contains('id', $role->id);
    }

    /**
     * Check if the user has any of the given roles
     * 
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Assign a role to this user
     * 
     * @param Role|array|int $role
     * @return $this
     */
    public function assignRole($role)
    {
        $this->roles()->attach($role);
        return $this;
    }

    /**
     * Remove a role from this user
     * 
     * @param Role|array|int $role
     * @return $this
     */
    public function removeRole($role)
    {
        $this->roles()->detach($role);
        return $this;
    }

    /**
     * Sync the roles of this user
     * 
     * @param array $roles
     * @return $this
     */
    public function syncRoles(array $roles)
    {
        $this->roles()->sync($roles);
        return $this;
    }

    /**
     * Check if the user has a specific permission through their roles
     * 
     * @param string $permission Permission slug
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $perm) {
                if ($perm->slug === $permission) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if the user has any of the given permissions
     * 
     * @param array $permissions Array of permission slugs
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all permissions the user has through their roles
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        $permissions = collect();
        
        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        
        return $permissions->unique('id');
    }
}