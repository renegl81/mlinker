<?php

namespace App\Models;

use App\Events\MenuActivated;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Menu extends Model
{
    use BelongsToTenant, HasFactory, HasImage, HasTranslations;

    protected static function booted(): void
    {
        static::updating(function (Menu $menu): void {
            if ($menu->isDirty('is_active') && $menu->is_active === true) {
                MenuActivated::dispatch($menu);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'location_id',
        'template_id',
        'is_active',
        'lang',
        'image_url',
        'translations',
        'show_prices',
        'show_currency',
        'show_calories',
        'customization',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'image_path',
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
            'location_id' => 'integer',
            'show_prices' => 'boolean',
            'show_currency' => 'boolean',
            'show_calories' => 'boolean',
            'translations' => 'array',
            'customization' => 'array',
        ];
    }

    /**
     * Get the full storage path for the menu image.
     */
    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image_url) {
                    return null;
                }

                // Si la imagen ya es una URL completa (base64 o http), retornarla directamente
                if (str_starts_with($this->image_url, 'data:') || str_starts_with($this->image_url, 'http')) {
                    return $this->image_url;
                }

                // Obtener el ID del tenant actual
                $tenantId = tenant('id');

                return rtrim(config('app.url'), '/').route('tenant_image', ['tenant' => 'tenant'.$tenantId, 'path' => $this->image_url], false);
            }
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QRCode::class);
    }
}
