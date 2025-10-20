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
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer'
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            if (empty($recipe->slug)) {
                $recipe->slug = static::generateUniqueSlug($recipe->name);
            }
        });

        static::updating(function ($recipe) {
            if ($recipe->isDirty('name')) {
                $recipe->slug = static::generateUniqueSlug($recipe->name, $recipe->id);
            }
        });
    }

    /**
     * Generate unique slug
     */
    public static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = static::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope for published recipes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get total cooking time
     */
    public function getTotalTimeAttribute()
    {
        return $this->prep_time + $this->cook_time;
    }

    /**
     * Get difficulty badge color
     */
    public function getDifficultyColorAttribute()
    {
        switch ($this->difficulty) {
            case 'mudah':
                return 'success';
            case 'sedang':
                return 'warning';
            case 'sulit':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('assets/img/no-image.png'); // Default image
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}