<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Boot — auto-generate slug on create/update
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    /**
     * Generate a URL-safe slug, ensuring uniqueness within the table.
     */
    protected static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        $query = static::withTrashed()->where('slug', $slug);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        while ($query->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
            $query = static::withTrashed()->where('slug', $slug);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
        }

        return $slug;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Products belonging to this category.
     * Uses withTrashed so soft-deleted products remain accessible for reports.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Active (non-deleted) products only.
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Total number of products in this category (including soft-deleted).
     */
    public function getProductCountAttribute(): int
    {
        return $this->products()->withTrashed()->count();
    }
}
