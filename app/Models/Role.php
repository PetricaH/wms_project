<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the users that have this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the permissions assigned to this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if the role has a specific permission
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }
        
        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Assign a permission to this role
     *
     * @param Permission|array|int $permission
     * @return $this
     */
    public function givePermissionTo($permission)
    {
        $this->permissions()->attach($permission);
        return $this;
    }

    /**
     * Remove a permission from this role
     *
     * @param Permission|array|int $permission
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($permission);
        return $this;
    }

    /**
     * Sync the permissions of this role
     *
     * @param array $permissions
     * @return $this
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->sync($permissions);
        return $this;
    }
}