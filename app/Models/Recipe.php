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
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'ingredients',
        'instructions',
        'notes',
        'image',
        'status'
    ];

    // Cast JSON fields
    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
    ];

    // Accessor untuk ingredients yang sudah diformat
    public function getFormattedIngredientsAttribute()
    {
        if (!is_array($this->ingredients)) {
            return [];
        }

        $formatted = [];
        
        foreach ($this->ingredients as $ingredient) {
            if (is_array($ingredient) && isset($ingredient['item'])) {
                // Format: "qty unit - item (section)"
                $text = '';
                
                if (isset($ingredient['qty']) && $ingredient['qty'] > 0) {
                    $text .= number_format($ingredient['qty'], $ingredient['qty'] == (int)$ingredient['qty'] ? 0 : 1);
                }
                
                if (isset($ingredient['unit']) && !empty($ingredient['unit'])) {
                    $text .= ' ' . $ingredient['unit'];
                }
                
                $text .= ' ' . $ingredient['item'];
                
                if (isset($ingredient['section']) && !empty($ingredient['section'])) {
                    $text .= ' (' . $ingredient['section'] . ')';
                }
                
                $formatted[] = trim($text);
            } else {
                // Jika format simple string
                $formatted[] = is_string($ingredient) ? $ingredient : '';
            }
        }
        
        return $formatted;
    }

    // Accessor untuk instructions yang sudah diformat
    public function getFormattedInstructionsAttribute()
    {
        if (!is_array($this->instructions)) {
            return [];
        }

        $formatted = [];
        
        foreach ($this->instructions as $instruction) {
            if (is_array($instruction) && isset($instruction['step'])) {
                $formatted[] = $instruction['step'];
            } else {
                $formatted[] = is_string($instruction) ? $instruction : '';
            }
        }
        
        return $formatted;
    }

    // Helper untuk grouping ingredients by section
    public function getIngredientsBySectionAttribute()
    {
        if (!is_array($this->ingredients)) {
            return [];
        }

        $grouped = [];
        
        foreach ($this->ingredients as $ingredient) {
            if (is_array($ingredient) && isset($ingredient['item'])) {
                $section = $ingredient['section'] ?? 'Bahan Utama';
                
                if (!isset($grouped[$section])) {
                    $grouped[$section] = [];
                }
                
                $text = '';
                if (isset($ingredient['qty']) && $ingredient['qty'] > 0) {
                    $text .= number_format($ingredient['qty'], $ingredient['qty'] == (int)$ingredient['qty'] ? 0 : 1);
                }
                
                if (isset($ingredient['unit']) && !empty($ingredient['unit'])) {
                    $text .= ' ' . $ingredient['unit'];
                }
                
                $text .= ' ' . $ingredient['item'];
                
                $grouped[$section][] = trim($text);
            }
        }
        
        return $grouped;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Other accessors
    public function getTotalTimeAttribute()
    {
        return $this->prep_time + $this->cook_time;
    }

    public function getDifficultyBadgeAttribute()
    {
        switch ($this->difficulty) {
            case 'mudah':
                return 'success';
            case 'sedang':
                return 'warning';
            case 'sulit':
                return 'danger';
            default:
                return 'primary';
        }
    }
}