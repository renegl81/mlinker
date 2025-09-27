<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Http\Requests\PaymentUpdateRequest;
use App\Jobs\CreatePayment;
use App\Jobs\DeletePayment;
use App\Jobs\ListPayments;
use App\Jobs\ShowPayment;
use App\Jobs\UpdatePayment;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        ListPayments::dispatch();

        return Inertia::render('payment.index', [
            'payments' => $payments,
        ]);
    }

    public function store(PaymentStoreRequest $request): RedirectResponse
    {
        CreatePayment::dispatch($request);

        return redirect()->route('payment.index');
    }

    public function show(Request $request, Payment $payment)
    {
        ShowPayment::dispatch($id);

        return Inertia::render('payment.show', [
            'payment' => $payment,
        ]);
    }

    public function update(PaymentUpdateRequest $request, Payment $payment): RedirectResponse
    {
        UpdatePayment::dispatch($request, $id);

        return redirect()->route('payment.show', ['payment' => $payment]);
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        DeletePayment::dispatch($id);

        return redirect()->route('payment.index');
    }
}
