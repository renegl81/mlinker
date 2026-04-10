<?php

namespace App\Models;

use App\Models\Traits\HasImage;
use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant, HasFactory, HasImage, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'calories',
        'tags',
        'image_url',
    ];

    protected $appends = [
        'image_path',
    ];

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'price' => 'decimal:2',
            'calories' => 'decimal:2',
            'tags' => 'array',
            'translations' => 'array',
        ];
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class);
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
