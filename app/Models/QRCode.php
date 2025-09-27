<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_card_id',
        'parameters',
        'image_url',
        'url',
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
            'parameters' => 'array',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    public function menuCard(): BelongsTo
    {
        return $this->belongsTo(MenuCard::class);
    }
}
