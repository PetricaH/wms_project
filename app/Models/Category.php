<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    // the attributes that are mass assignable
    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'is_active',
    ];

    // the attributes that should be cast
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // get the parent category that owns the category
    public function parent(): BelongsTo {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // get the subcategories for the category
    public function children(): HasMany {
        return $this->hasMany(Category::class, 'parentid');
    }

    // get the products for the category
    public function products(): HasMany {
        return $this->hasMany(Product::class);
    }

    // scope the query to only include active categories
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
