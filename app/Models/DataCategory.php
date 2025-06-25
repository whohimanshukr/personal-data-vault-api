<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the personal data items for this category
     */
    public function personalData(): HasMany
    {
        return $this->hasMany(PersonalData::class, 'category_id');
    }

    /**
     * Get the user that owns this category
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 