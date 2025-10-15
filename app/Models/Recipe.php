<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'ingredients',
        'instructions',
        'notes',
        'status'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            if (empty($recipe->slug)) {
                $recipe->slug = Str::slug($recipe->name);
            }
        });

        static::updating(function ($recipe) {
            if ($recipe->isDirty('name')) {
                $recipe->slug = Str::slug($recipe->name);
            }
        });
    }

    public function getTotalTimeAttribute()
    {
        return $this->prep_time + $this->cook_time;
    }

    public function getDifficultyBadgeAttribute()
    {
        $badges = [
            'mudah' => 'success',
            'sedang' => 'warning',
            'sulit' => 'danger'
        ];

        return $badges[$this->difficulty] ?? 'secondary';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}