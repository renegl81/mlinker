<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreatePlan;
use App\Jobs\DeletePlan;
use App\Jobs\ListPlans;
use App\Jobs\ShowPlan;
use App\Jobs\UpdatePlan;
use App\Models\Plan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PlanController
 */
final class PlanControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('plans.index'));

        $response->assertOk();
        $response->assertViewIs('plan.index');
        $response->assertViewHas('plans');

        Queue::assertPushed(ListPlans::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PlanController::class,
            'store',
            \App\Http\Requests\PlanStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $period = fake()->word();

        Queue::fake();

        $response = $this->post(route('plans.store'), [
            'name' => $name,
            'price' => $price,
            'period' => $period,
        ]);

        $response->assertRedirect(route('plan.index'));

        Queue::assertPushed(CreatePlan::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $plan = Plan::factory()->create();

        Queue::fake();

        $response = $this->get(route('plans.show', $plan));

        $response->assertOk();
        $response->assertViewIs('plan.show');
        $response->assertViewHas('plan');

        Queue::assertPushed(ShowPlan::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PlanController::class,
            'update',
            \App\Http\Requests\PlanUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $plan = Plan::factory()->create();
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);
        $period = fake()->word();

        Queue::fake();

        $response = $this->put(route('plans.update', $plan), [
            'name' => $name,
            'price' => $price,
            'period' => $period,
        ]);

        $response->assertRedirect(route('plan.show', ['plan' => $plan]));

        Queue::assertPushed(UpdatePlan::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $plan = Plan::factory()->create();

        Queue::fake();

        $response = $this->delete(route('plans.destroy', $plan));

        $response->assertRedirect(route('plan.index'));

        Queue::assertPushed(DeletePlan::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
