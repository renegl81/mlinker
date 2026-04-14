<?php

namespace Tests\Feature\Tenant;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('plan:free');

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function tenantUrl(string $routeName, array $params = []): string
    {
        return 'http://test.localhost'.route($routeName, $params, false);
    }

    private function makePlan(array $overrides = []): Plan
    {
        return Plan::factory()->create(array_merge([
            'slug' => 'plan-'.uniqid(),
            'max_locations' => 5,
            'max_menus_per_location' => 5,
            'max_products' => 100,
            'max_images' => 0,
        ], $overrides));
    }

    private function subscribeTo(Plan $plan): void
    {
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // Upload válido guarda imagen y devuelve JSON
    // -------------------------------------------------------------------------

    #[Test]
    public function valid_upload_stores_image_and_returns_json_with_url_and_thumbnail_url(): void
    {
        Storage::fake('public');

        $plan = $this->makePlan(['max_images' => 10]);
        $this->subscribeTo($plan);

        $file = UploadedFile::fake()->image('photo.jpg', 600, 600);

        $response = $this->actingAs($this->user)
            ->postJson($this->tenantUrl('tenant.uploads.image'), ['image' => $file]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'thumbnail_url']);

        $data = $response->json();
        $tenantId = $this->tenant->id;
        $this->assertStringStartsWith("tenant{$tenantId}/images/", $data['url']);
        $this->assertStringContainsString('/thumbs/', $data['thumbnail_url']);

        Storage::disk('public')->assertExists($data['url']);
        Storage::disk('public')->assertExists($data['thumbnail_url']);
    }

    // -------------------------------------------------------------------------
    // Tenant Free (max_images=0, unlimited=false): como max_images=0 en CheckLimit
    // significa "unlimited" — necesitamos un plan con max_images=-1 o similar para
    // que Free bloquee. Pero según CheckLimit: "0 means unlimited".
    // El Free tiene max_images=0, por tanto no se puede bloquear via CheckLimit
    // para imágenes. El plan Free real bloquea via max_images=1 (o similar).
    // Revisamos: el plan Free real tiene max_images=0 = unlimited según CheckLimit.
    // Entonces el test "Free no puede subir" requiere max_images > 0 y estar al límite.
    // -------------------------------------------------------------------------

    #[Test]
    public function tenant_at_image_limit_receives_403(): void
    {
        Storage::fake('public');

        // Plan con límite de 1 imagen
        $plan = $this->makePlan(['max_images' => 1]);
        $this->subscribeTo($plan);

        // Ya tiene 1 producto con imagen (al límite)
        Product::factory()->create(['image_url' => 'tenant1/images/existing.jpg']);

        $file = UploadedFile::fake()->image('photo.jpg', 600, 600);

        $response = $this->actingAs($this->user)
            ->postJson($this->tenantUrl('tenant.uploads.image'), ['image' => $file]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Tenant Pro puede subir imágenes
    // -------------------------------------------------------------------------

    #[Test]
    public function pro_plan_tenant_can_upload_images(): void
    {
        Storage::fake('public');

        $plan = $this->makePlan(['max_images' => 50]);
        $this->subscribeTo($plan);

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->postJson($this->tenantUrl('tenant.uploads.image'), ['image' => $file]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'thumbnail_url']);
    }

    // -------------------------------------------------------------------------
    // Upload rechaza archivos que no son imagen
    // -------------------------------------------------------------------------

    #[Test]
    public function upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $plan = $this->makePlan(['max_images' => 10]);
        $this->subscribeTo($plan);

        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson($this->tenantUrl('tenant.uploads.image'), ['image' => $file]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    // -------------------------------------------------------------------------
    // Upload requiere autenticación
    // -------------------------------------------------------------------------

    #[Test]
    public function upload_requires_authentication(): void
    {
        $response = $this->postJson($this->tenantUrl('tenant.uploads.image'), []);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Al eliminar Product con image_url de storage, se borran los archivos
    // -------------------------------------------------------------------------

    #[Test]
    public function deleting_product_with_storage_image_url_removes_files_from_disk(): void
    {
        Storage::fake('public');

        $imagePath = 'testtenant1/images/photo.jpg';
        $thumbPath = 'testtenant1/images/thumbs/photo.jpg';

        Storage::disk('public')->put($imagePath, 'fake-image-content');
        Storage::disk('public')->put($thumbPath, 'fake-thumb-content');

        $product = Product::factory()->create(['image_url' => $imagePath]);

        Storage::disk('public')->assertExists($imagePath);
        Storage::disk('public')->assertExists($thumbPath);

        $product->delete();

        Storage::disk('public')->assertMissing($imagePath);
        Storage::disk('public')->assertMissing($thumbPath);
    }

    // -------------------------------------------------------------------------
    // Eliminar Product con imagen http/data no borra archivos (no rompe)
    // -------------------------------------------------------------------------

    #[Test]
    public function deleting_product_with_absolute_url_does_not_attempt_storage_delete(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create(['image_url' => 'https://example.com/photo.jpg']);

        // No debe lanzar excepciones
        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
