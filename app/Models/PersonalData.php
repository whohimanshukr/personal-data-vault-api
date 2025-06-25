<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalData extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'data_type',
        'encrypted_data',
        'tags',
        'is_favorite',
        'category_id',
        'user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_favorite' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category that owns this personal data
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DataCategory::class, 'category_id');
    }

    /**
     * Get the user that owns this personal data
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by favorite status
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to search by title or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('tags', 'like', "%{$search}%");
        });
    }
} 