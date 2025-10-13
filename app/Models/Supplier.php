<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'address'
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Accessors
    public function getFullContactAttribute()
    {
        return $this->contact_person . ' (' . $this->phone . ')';
    }

    // Scopes
    public function scopeWithContact($query)
    {
        return $query->whereNotNull('contact_person');
    }
}