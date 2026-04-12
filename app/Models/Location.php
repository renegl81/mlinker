<?php

namespace App\Models;

use App\Models\Traits\HasDynamicFilters;
use App\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant, HasDynamicFilters, HasFactory, HasImage;

    protected $appends = ['image_path'];

    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image_url) {
                    return null;
                }

                if (str_starts_with($this->image_url, 'data:') || str_starts_with($this->image_url, 'http')) {
                    return $this->image_url;
                }

                $tenantId = tenant('id');

                return rtrim(config('app.url'), '/').route('tenant_image', ['tenant' => 'tenant'.$tenantId, 'path' => $this->image_url], false);
            }
        );
    }

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
        'primary_color',
        'secondary_color',
        'order_email',
        'order_whatsapp',
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
