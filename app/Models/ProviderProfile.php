<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProviderProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name_ar',
        'full_name_en',
        'phone_number',
        'email',
        'business_name_ar',
        'business_name_en',
        'unified_number',
        'business_category',
        'address_ar',
        'address_en',
        'city',
        'region',
        'location',
        'logo_path',
    ];

    protected function casts(): array
    {
        return ['business_category' => 'array'];
    }

    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->logo_path;
        if ($path === null || $path === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        $segments = array_values(array_filter(explode('/', $normalized), static fn ($s) => $s !== ''));
        $encoded = array_map(static fn (string $segment): string => rawurlencode($segment), $segments);

        if (file_exists(public_path('images/seed/'.$normalized))) {
            return asset('images/seed/'.implode('/', $encoded));
        }

        return asset('storage/'.implode('/', $encoded));
    }

    public function ewallet(): HasOne
    {
        return $this->hasOne(Ewallet::class, 'owner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProviderMenuItem::class, 'provider_id', 'user_id');
    }

    protected static function booted(): void
    {
        static::created(function (self $profile) {
            if (! $profile->ewallet) {
                $profile->ewallet()->create([
                    'owner_type' => 'PROVIDER',
                    'balance' => 0,
                    'status' => true,
                ]);
            }
        });
    }
}
