<?php

namespace Tests\Feature\Actions;

use App\Actions\Location\CreateLocation;
use App\Actions\Location\DeleteLocation;
use App\Actions\Location\UpdateLocation;
use App\Models\Country;
use App\Models\Location;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class LocationActionsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'action-test-tenant']);
        $this->tenant->domains()->create(['domain' => 'action-test.localhost']);

        tenancy()->initialize($this->tenant);

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->country = Country::factory()->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cafetería Central',
            'description' => 'Una cafetería en el centro',
            'address' => 'Calle Mayor, 10',
            'phone' => '+34 600 000 000',
            'city' => 'Madrid',
            'province' => 'Madrid',
            'postal_code' => '28001',
            'country_id' => $this->country->id,
            'currency' => 'EUR',
            'time_zone' => 'Europe/Madrid',
            'time_format' => 'H:i',
            'lang' => 'es',
            'languages' => ['es'],
            'social_medias' => [],
            'latitude' => null,
            'longitude' => null,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // CreateLocation
    // -------------------------------------------------------------------------

    #[Test]
    public function create_location_stores_record_with_correct_tenant_id(): void
    {
        $location = (new CreateLocation)->execute($this->validData());

        $this->assertInstanceOf(Location::class, $location);
        $this->assertDatabaseHas('locations', [
            'name' => 'Cafetería Central',
            'tenant_id' => 'action-test-tenant',
        ]);
    }

    #[Test]
    public function create_location_sets_slug_from_name(): void
    {
        $location = (new CreateLocation)->execute($this->validData(['name' => 'Mi Restaurante']));

        $this->assertEquals('mi-restaurante', $location->slug);
    }

    #[Test]
    public function create_location_sets_user_id_from_auth(): void
    {
        $location = (new CreateLocation)->execute($this->validData());

        $this->assertEquals($this->user->id, $location->user_id);
    }

    #[Test]
    public function create_location_uses_default_currency_when_not_provided(): void
    {
        $data = $this->validData();
        unset($data['currency']);

        $location = (new CreateLocation)->execute($data);

        $this->assertEquals('EUR', $location->currency);
    }

    // -------------------------------------------------------------------------
    // UpdateLocation
    // -------------------------------------------------------------------------

    #[Test]
    public function update_location_modifies_expected_fields(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'action-test-tenant']);

        $updated = (new UpdateLocation)->execute($location, [
            'name' => 'Nuevo Nombre',
            'city' => 'Barcelona',
            'address' => $location->address,
            'phone' => $location->phone,
            'province' => $location->province,
            'postal_code' => $location->postal_code,
            'country_id' => $location->country_id,
        ]);

        $this->assertEquals('Nuevo Nombre', $updated->name);
        $this->assertEquals('Barcelona', $updated->city);
    }

    #[Test]
    public function update_location_preserves_unmodified_fields(): void
    {
        $location = Location::factory()->create([
            'tenant_id' => 'action-test-tenant',
            'phone' => '+34 600 111 111',
        ]);

        $updated = (new UpdateLocation)->execute($location, [
            'name' => 'Nombre Cambiado',
        ]);

        $this->assertEquals('+34 600 111 111', $updated->phone);
    }

    #[Test]
    public function update_location_returns_fresh_model(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'action-test-tenant']);

        $result = (new UpdateLocation)->execute($location, ['name' => 'Actualizado']);

        $this->assertInstanceOf(Location::class, $result);
        $this->assertEquals('Actualizado', $result->name);
    }

    // -------------------------------------------------------------------------
    // DeleteLocation
    // -------------------------------------------------------------------------

    #[Test]
    public function delete_location_removes_record_from_database(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'action-test-tenant']);
        $locationId = $location->id;

        $result = (new DeleteLocation)->execute($location);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('locations', ['id' => $locationId]);
    }

    #[Test]
    public function delete_location_returns_true_on_success(): void
    {
        $location = Location::factory()->create(['tenant_id' => 'action-test-tenant']);

        $result = (new DeleteLocation)->execute($location);

        $this->assertTrue($result);
    }
}
