<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'country_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'is_admin',
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
            'email_verified_at' => 'timestamp',
            'country_id' => 'integer',
        ];
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->hasRole('Admin')
        );
    }
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Los restaurantes a los que pertenece este usuario.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'tenant_user',
            'user_id',
            'tenant_id'
        )
            ->withPivot('role', 'permissions', 'is_active', 'invited_at', 'joined_at')
            ->withTimestamps();
    }

    public function currentTenantRole()
    {
        if (!tenancy()->initialized) {
            return null;
        }

        return $this->roleInTenant(tenant('id'));
    }

    public function belongsToCurrentTenant(): bool
    {
        if (!tenancy()->initialized) {
            return false;
        }

        return $this->tenants()
            ->where('tenant_id', tenant('id'))
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Obtener rol en un tenant específico
     */
    public function roleInTenant($tenantId)
    {
        $tenant = $this->tenants()->where('tenant_id', $tenantId)->first();
        return $tenant?->pivot->role;
    }

    /**
     * Helper para saber si es dueño de un tenant específico
     */
    public function owns(Tenant $tenant): bool
    {
        return $this->tenants()
            ->where('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function assignRole($role): User
    {
        $this->roles()->attach($role);

        return $this;
    }
}
