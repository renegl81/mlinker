<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'menu_card_id',
        'image_url',
        'show_prices',
        'show_currency',
        'show_calories',
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
            'menu_card_id' => 'integer',
            'show_prices' => 'boolean',
            'show_currency' => 'boolean',
            'show_calories' => 'boolean',
        ];
    }

    public function menuCard(): BelongsTo
    {
        return $this->belongsTo(MenuCard::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
