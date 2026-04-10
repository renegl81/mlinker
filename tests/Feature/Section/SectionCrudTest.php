<?php

declare(strict_types=1);

namespace Tests\Feature\Section;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class SectionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->menu = Menu::factory()->create(['is_active' => true]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    protected function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://test.localhost'.route($routeName, $params, false);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Store
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function store_creates_section_with_correct_sort_order(): void
    {
        // First section
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.sections.store', ['menu' => $this->menu->id]), [
                'name' => 'Entrantes',
            ]);

        $response->assertRedirect();

        $first = Section::where('name', 'Entrantes')->first();
        $this->assertNotNull($first);
        $this->assertEquals(1, $first->sort_order);

        // Second section should have sort_order = 2
        $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.sections.store', ['menu' => $this->menu->id]), [
                'name' => 'Principales',
            ]);

        $second = Section::where('name', 'Principales')->first();
        $this->assertEquals(2, $second->sort_order);
    }

    #[Test]
    public function store_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.sections.store', ['menu' => $this->menu->id]), []);

        $response->assertSessionHasErrors('name');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Update
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function update_changes_section_name(): void
    {
        $section = Section::create([
            'name' => 'Old name',
            'menu_id' => $this->menu->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->put($this->tenantUrl('tenant.sections.update', ['section' => $section->id]), [
                'name' => 'New name',
            ]);

        $response->assertRedirect();
        $this->assertEquals('New name', $section->fresh()->name);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Destroy
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function destroy_deletes_section(): void
    {
        $section = Section::create([
            'name' => 'To delete',
            'menu_id' => $this->menu->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->tenantUrl('tenant.sections.destroy', ['section' => $section->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Reorder
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function reorder_updates_sort_order(): void
    {
        $s1 = Section::create(['name' => 'A', 'menu_id' => $this->menu->id, 'tenant_id' => $this->tenant->id, 'sort_order' => 1]);
        $s2 = Section::create(['name' => 'B', 'menu_id' => $this->menu->id, 'tenant_id' => $this->tenant->id, 'sort_order' => 2]);
        $s3 = Section::create(['name' => 'C', 'menu_id' => $this->menu->id, 'tenant_id' => $this->tenant->id, 'sort_order' => 3]);

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('tenant.menus.sections.reorder', ['menu' => $this->menu->id]), [
                'section_ids' => [$s3->id, $s1->id, $s2->id],
            ]);

        $response->assertRedirect();

        $this->assertEquals(1, $s3->fresh()->sort_order);
        $this->assertEquals(2, $s1->fresh()->sort_order);
        $this->assertEquals(3, $s2->fresh()->sort_order);
    }
}
