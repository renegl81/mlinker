<?php

namespace App\Models;

use App\Models\Traits\HasDynamicFilters;
use App\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant, HasDynamicFilters, HasFactory, HasImage;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'description',
        'user_id',
        'country_id',
        'image_url',
        'logo_url',
        'slug',
        'url',
        'lang',
        'languages',
        'currency',
        'time_format',
        'time_zone',
        'social_medias',
        'latitude',
        'longitude',
    ];

    public static function getFilterableFields(): array
    {
        return [
            'name' => 'like',
            'address' => 'like',
            'city' => 'like',
            'province' => 'like',
            'status' => '=',
            'type' => 'in',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'country_id' => 'integer',
            'languages' => 'array',
            'social_medias' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class)->orderBy('weekday');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
