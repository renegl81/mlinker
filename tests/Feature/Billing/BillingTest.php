<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Subscription\ChangeSubscription;
use App\Actions\Subscription\StartCheckout;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('plan:free');

        Event::fake([TenantCreated::class, TenantDeleted::class]);

        $this->tenant = Tenant::create(['id' => 'test-billing-tenant']);
        $this->tenant->domains()->create(['domain' => 'test.localhost']);

        tenancy()->initialize($this->tenant);

        $this->tenant->onboarding_completed_at = now();
        $this->tenant->save();

        Role::query()->firstOrCreate(['name' => 'Owner']);
        $this->user = User::factory()->create();

        $this->freePlan = Plan::factory()->create([
            'slug' => 'free',
            'name' => 'Free',
            'price' => '0.00',
            'stripe_price_id' => null,
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function tenantUrl(string $path): string
    {
        return 'http://test.localhost'.$path;
    }

    private function subscribeFree(): Subscription
    {
        return Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->freePlan->id,
            'type' => 'default',
            'stripe_status' => 'free',
            'quantity' => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // Plans page
    // -------------------------------------------------------------------------

    #[Test]
    public function plans_page_renders_for_authenticated_user(): void
    {
        $this->subscribeFree();

        Plan::factory()->create([
            'slug' => 'pro',
            'name' => 'Pro',
            'price' => '14.99',
            'stripe_price_id' => 'price_pro',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel/billing/plans'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('admin/tenant/billing/Plans')
                ->has('plans')
                ->has('currentSubscription');
        });
    }

    #[Test]
    public function plans_page_shows_all_active_plans(): void
    {
        $this->subscribeFree();

        Plan::factory()->create([
            'slug' => 'pro',
            'name' => 'Pro',
            'price' => '14.99',
            'stripe_price_id' => 'price_pro',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Plan::factory()->create([
            'slug' => 'inactive',
            'name' => 'Inactive',
            'price' => '9.99',
            'stripe_price_id' => 'price_inactive',
            'is_active' => false,
            'sort_order' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel/billing/plans'));

        $response->assertInertia(function ($page) {
            $page->component('admin/tenant/billing/Plans')
                ->where('plans', function ($plans) {
                    // Only active plans
                    return collect($plans)->every(fn ($p) => $p['is_active'] === true);
                });
        });
    }

    #[Test]
    public function free_tenant_sees_current_plan_in_plans_page(): void
    {
        $this->subscribeFree();

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel/billing/plans'));

        $response->assertInertia(function ($page) {
            $page->component('admin/tenant/billing/Plans')
                ->where('currentSubscription.stripe_status', 'free');
        });
    }

    // -------------------------------------------------------------------------
    // Manage page
    // -------------------------------------------------------------------------

    #[Test]
    public function manage_page_renders_with_current_subscription(): void
    {
        $this->subscribeFree();

        $response = $this->actingAs($this->user)
            ->get($this->tenantUrl('/panel/billing/manage'));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('admin/tenant/billing/Manage')
                ->has('subscription');
        });
    }

    // -------------------------------------------------------------------------
    // Checkout
    // -------------------------------------------------------------------------

    #[Test]
    public function checkout_fails_for_plan_without_stripe_price_id(): void
    {
        $this->subscribeFree();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('/panel/billing/checkout'), [
                'plan_slug' => 'free',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function checkout_fails_for_nonexistent_plan(): void
    {
        $this->subscribeFree();

        $response = $this->actingAs($this->user)
            ->post($this->tenantUrl('/panel/billing/checkout'), [
                'plan_slug' => 'nonexistent-plan',
            ]);

        $response->assertSessionHasErrors('plan_slug');
    }

    // -------------------------------------------------------------------------
    // StartCheckout action unit tests
    // -------------------------------------------------------------------------

    #[Test]
    public function start_checkout_throws_if_plan_has_no_stripe_price_id(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/no tiene un precio de Stripe/');

        $action = new StartCheckout;
        $action->execute(
            $this->tenant,
            $this->freePlan,
            'https://example.com/success',
            'https://example.com/cancel',
        );
    }

    #[Test]
    public function start_checkout_throws_if_already_on_same_plan(): void
    {
        $proPlan = Plan::factory()->create([
            'slug' => 'pro',
            'stripe_price_id' => 'price_pro',
        ]);

        // Manually create a paid subscription on the pro plan
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $proPlan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'stripe_id' => 'sub_test123',
            'stripe_price' => 'price_pro',
            'quantity' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Ya estás suscrito/');

        $action = new StartCheckout;
        $action->execute(
            $this->tenant,
            $proPlan,
            'https://example.com/success',
            'https://example.com/cancel',
        );
    }

    // -------------------------------------------------------------------------
    // ChangeSubscription action unit tests
    // -------------------------------------------------------------------------

    #[Test]
    public function change_subscription_cancel_throws_on_free_plan(): void
    {
        $this->subscribeFree();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/No hay una suscripción de pago/');

        $action = new ChangeSubscription;
        $action->cancel($this->tenant);
    }

    #[Test]
    public function change_subscription_resume_throws_when_no_subscription(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/No hay ninguna suscripción/');

        $action = new ChangeSubscription;
        $action->resume($this->tenant);
    }

    // -------------------------------------------------------------------------
    // Webhook: subscription deleted → degrade to Free
    // -------------------------------------------------------------------------

    #[Test]
    public function webhook_subscription_deleted_degrades_to_free_plan(): void
    {
        $proPlan = Plan::factory()->create([
            'slug' => 'pro',
            'stripe_price_id' => 'price_pro',
        ]);

        $subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $proPlan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'stripe_id' => 'sub_todelete',
            'stripe_price' => 'price_pro',
            'quantity' => 1,
        ]);

        // Simulate the webhook payload
        $payload = [
            'data' => [
                'object' => [
                    'id' => 'sub_todelete',
                    'customer' => 'cus_test123',
                    'items' => [
                        'data' => [
                            ['price' => ['id' => 'price_pro']],
                        ],
                    ],
                ],
            ],
        ];

        // Call the method directly (skip Stripe signature verification)
        $controller = new StripeWebhookController;
        $controller->handleCustomerSubscriptionDeleted($payload);

        $subscription->refresh();

        $this->assertEquals($this->freePlan->id, $subscription->plan_id);
        $this->assertEquals('free', $subscription->stripe_status);
        $this->assertNull($subscription->stripe_id);
    }

    #[Test]
    public function webhook_subscription_updated_syncs_plan_id(): void
    {
        $proPlan = Plan::factory()->create([
            'slug' => 'pro',
            'stripe_price_id' => 'price_pro_123',
        ]);

        $businessPlan = Plan::factory()->create([
            'slug' => 'business',
            'stripe_price_id' => 'price_business_456',
        ]);

        $subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $proPlan->id,
            'type' => 'default',
            'stripe_status' => 'active',
            'stripe_id' => 'sub_toswap',
            'stripe_price' => 'price_pro_123',
            'quantity' => 1,
        ]);

        // Payload simulating a swap to the business plan
        $payload = [
            'data' => [
                'object' => [
                    'id' => 'sub_toswap',
                    'customer' => 'cus_test123',
                    'status' => 'active',
                    'items' => [
                        'data' => [
                            ['price' => ['id' => 'price_business_456']],
                        ],
                    ],
                    'cancel_at_period_end' => false,
                    'current_period_end' => now()->addMonth()->timestamp,
                    'current_period_start' => now()->timestamp,
                    'quantity' => 1,
                    'trial_end' => null,
                ],
            ],
        ];

        $controller = new StripeWebhookController;
        $controller->handleCustomerSubscriptionUpdated($payload);

        $subscription->refresh();

        $this->assertEquals($businessPlan->id, $subscription->plan_id);
    }
}
