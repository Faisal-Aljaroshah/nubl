<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderMenuItem extends Model
{
    protected $table = 'provider_menu_items';

    protected $fillable = [
        'provider_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'category',
        'sku',
        'max_per_request',
        'category_id',
        'is_active',
        'image_path',
        'is_admin_blocked',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_admin_blocked' => 'boolean',
        ];
    }

    /**
     * Display name for the current app locale (Arabic when set and locale is ar).
     */
    public function getLocalizedNameAttribute(): string
    {
        if (app()->getLocale() === 'ar' && filled($this->name_ar)) {
            return (string) $this->name_ar;
        }

        return (string) ($this->name ?? '');
    }

    /**
     * Display description for the current app locale (Arabic when set and locale is ar).
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        if (app()->getLocale() === 'ar' && filled($this->description_ar)) {
            return (string) $this->description_ar;
        }

        return $this->description !== null ? (string) $this->description : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path;
        if ($path === null || $path === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        $segments = array_values(array_filter(explode('/', $normalized), static fn ($s) => $s !== ''));
        $encoded = array_map(static fn (string $segment): string => rawurlencode($segment), $segments);

        if (file_exists(public_path('images/'.$normalized))) {
            return asset('images/'.implode('/', $encoded));
        }

        return asset('storage/'.implode('/', $encoded));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function menuItemCategory(): BelongsTo
    {
        return $this->belongsTo(MenuItemCategory::class, 'category_id');
    }

    /**
     * Scope to only active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_admin_blocked', false);
    }

    /**
     * Scope to items owned by a specific provider.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('provider_id', $userId);
    }
}
