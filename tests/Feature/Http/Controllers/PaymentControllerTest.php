<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreatePayment;
use App\Jobs\DeletePayment;
use App\Jobs\ListPayments;
use App\Jobs\ShowPayment;
use App\Jobs\UpdatePayment;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PaymentController
 */
final class PaymentControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('payments.index'));

        $response->assertOk();
        $response->assertViewIs('payment.index');
        $response->assertViewHas('payments');

        Queue::assertPushed(ListPayments::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PaymentController::class,
            'store',
            \App\Http\Requests\PaymentStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $subscription = Subscription::factory()->create();
        $amount = fake()->randomFloat(/** decimal_attributes **/);
        $paid_at = Carbon::parse(fake()->dateTime());
        $payment_method = fake()->word();
        $status = fake()->word();

        Queue::fake();

        $response = $this->post(route('payments.store'), [
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'paid_at' => $paid_at,
            'payment_method' => $payment_method,
            'status' => $status,
        ]);

        $response->assertRedirect(route('payment.index'));

        Queue::assertPushed(CreatePayment::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $payment = Payment::factory()->create();

        Queue::fake();

        $response = $this->get(route('payments.show', $payment));

        $response->assertOk();
        $response->assertViewIs('payment.show');
        $response->assertViewHas('payment');

        Queue::assertPushed(ShowPayment::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PaymentController::class,
            'update',
            \App\Http\Requests\PaymentUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $payment = Payment::factory()->create();
        $subscription = Subscription::factory()->create();
        $amount = fake()->randomFloat(/** decimal_attributes **/);
        $paid_at = Carbon::parse(fake()->dateTime());
        $payment_method = fake()->word();
        $status = fake()->word();

        Queue::fake();

        $response = $this->put(route('payments.update', $payment), [
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'paid_at' => $paid_at,
            'payment_method' => $payment_method,
            'status' => $status,
        ]);

        $response->assertRedirect(route('payment.show', ['payment' => $payment]));

        Queue::assertPushed(UpdatePayment::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $payment = Payment::factory()->create();

        Queue::fake();

        $response = $this->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('payment.index'));

        Queue::assertPushed(DeletePayment::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
