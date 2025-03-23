<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', // UUID
        'name',
        'data', // JSON column
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];
    
    /**
     * Get the domains associated with the tenant.
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
    
    /**
     * Get the users associated with the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the roles defined for this tenant.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}