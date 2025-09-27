<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateSubscription;
use App\Jobs\DeleteSubscription;
use App\Jobs\ListSubscriptions;
use App\Jobs\ShowSubscription;
use App\Jobs\UpdateSubscription;
use App\Models\;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SubscriptionController
 */
final class SubscriptionControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('subscriptions.index'));

        $response->assertOk();
        $response->assertViewIs('subscription.index');
        $response->assertViewHas('subscriptions');

        Queue::assertPushed(ListSubscriptions::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SubscriptionController::class,
            'store',
            \App\Http\Requests\SubscriptionStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $user = User::factory()->create();
        $plan = ::factory()->create();
        $started_at = Carbon::parse(fake()->dateTime());
        $status = fake()->word();

        Queue::fake();

        $response = $this->post(route('subscriptions.store'), [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => $started_at,
            'status' => $status,
        ]);

        $response->assertRedirect(route('subscription.index'));

        Queue::assertPushed(CreateSubscription::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $subscription = Subscription::factory()->create();

        Queue::fake();

        $response = $this->get(route('subscriptions.show', $subscription));

        $response->assertOk();
        $response->assertViewIs('subscription.show');
        $response->assertViewHas('subscription');

        Queue::assertPushed(ShowSubscription::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SubscriptionController::class,
            'update',
            \App\Http\Requests\SubscriptionUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $subscription = Subscription::factory()->create();
        $user = User::factory()->create();
        $plan = ::factory()->create();
        $started_at = Carbon::parse(fake()->dateTime());
        $status = fake()->word();

        Queue::fake();

        $response = $this->put(route('subscriptions.update', $subscription), [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => $started_at,
            'status' => $status,
        ]);

        $response->assertRedirect(route('subscription.show', ['subscription' => $subscription]));

        Queue::assertPushed(UpdateSubscription::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $subscription = Subscription::factory()->create();

        Queue::fake();

        $response = $this->delete(route('subscriptions.destroy', $subscription));

        $response->assertRedirect(route('subscription.index'));

        Queue::assertPushed(DeleteSubscription::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
