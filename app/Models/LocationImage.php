<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LocationImage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'location_id',
        'tenant_id',
        'path',
        'alt',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'location_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
