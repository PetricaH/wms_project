<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'is_default',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];
    
    /**
     * Get the business that owns the role.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    
    /**
     * Get the users assigned to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    /**
     * Get the permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
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
            $query->where('business_id', $tenant->id);
        });
    }
}