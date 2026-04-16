<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MenuView extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'tenant_id',
        'viewed_at',
        'ip',
        'user_agent',
        'referer',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'menu_id' => 'integer',
            'viewed_at' => 'datetime',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
