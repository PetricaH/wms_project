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
     *
     * Ensure all fields you intend to set via ::create() or ::update()
     * are listed here, especially 'tenant_id', 'slug', and 'is_default'
     * based on the AuthController logic.
     */
    protected $fillable = [
        'tenant_id',    // <-- Add this
        'name',
        'slug',         // <-- Add this
        'display_name', // Keep this if you use it
        'description',
        'is_default',   // <-- Add this
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        // Add other casts if necessary
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
        // Make sure the Permission model exists and is imported
        // use App\Models\Permission;
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Define the relationship to the Tenant model (if applicable)
     */
    // public function tenant()
    // {
    //    return $this->belongsTo(Tenant::class);
    // }


    /**
     * Check if the role has a specific permission
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        // Eager load permissions if checking multiple times to avoid N+1 queries
        // $this->loadMissing('permissions');

        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }

        // Assuming $permission is a Permission model instance
        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Assign a permission to this role
     *
     * @param Permission|array|int $permission Permission model instance, ID, or array of IDs
     * @return $this
     */
    public function givePermissionTo($permission)
    {
        // Use syncWithoutDetaching to avoid duplicate entries if called multiple times
        $this->permissions()->syncWithoutDetaching($permission);
        return $this;
    }

    /**
     * Remove a permission from this role
     *
     * @param Permission|array|int $permission Permission model instance, ID, or array of IDs
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($permission);
        return $this;
    }

    /**
     * Sync the permissions of this role.
     * Replaces all existing permissions with the given ones.
     *
     * @param array $permissions Array of Permission IDs
     * @return $this
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->sync($permissions);
        return $this;
    }
}
