<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PageView extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'page_type',
        'resource_id',
        'event',
        'viewed_at',
        'ip',
        'user_agent',
        'referer',
        'device_type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resource_id' => 'integer',
            'viewed_at' => 'datetime',
        ];
    }
}
