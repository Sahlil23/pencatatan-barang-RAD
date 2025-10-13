<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description',
        // 'is_active'
    ];

    // protected $casts = [
    //     'is_active' => 'boolean',
    // ];

    // Relationships
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Scopes
    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }

    // // Accessors
    // public function getStatusAttribute()
    // {
    //     return $this->is_active ? 'Active' : 'Inactive';
    // }
}