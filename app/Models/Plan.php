<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'price',
        'period',
        'description',
        'is_active',
        'stripe_price_id',
        'max_locations',
        'max_menus_per_location',
        'max_products',
        'max_images',
        'has_analytics',
        'has_custom_qr',
        'has_multilang',
        'has_api_access',
        'has_custom_domain',
        'show_branding',
        'trial_days',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'max_locations' => 'integer',
            'max_menus_per_location' => 'integer',
            'max_products' => 'integer',
            'max_images' => 'integer',
            'has_analytics' => 'boolean',
            'has_custom_qr' => 'boolean',
            'has_multilang' => 'boolean',
            'has_api_access' => 'boolean',
            'has_custom_domain' => 'boolean',
            'show_branding' => 'boolean',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope to filter the free plan.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('slug', 'free');
    }

    /**
     * Check if this plan allows a given feature (has_{$feature} must be true).
     */
    public function allows(string $feature): bool
    {
        $column = 'has_'.$feature;

        return (bool) ($this->{$column} ?? false);
    }

    /**
     * Return the free plan, cached for 60 minutes.
     * Returns null if the free plan has not been seeded yet.
     */
    public static function free(): ?self
    {
        return Cache::remember('plan:free', 3600, fn () => static::where('slug', 'free')->first());
    }
}
