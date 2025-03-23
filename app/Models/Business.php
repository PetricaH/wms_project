<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class Business extends Model
{
    use HasFactory;

    /**
     * Specify the connection to use (central database)
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'subdomain',
        'database_name',
        'logo',
        'settings',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the users associated with the business.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the roles defined for this business.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}