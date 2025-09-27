<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionStoreRequest;
use App\Http\Requests\SubscriptionUpdateRequest;
use App\Jobs\CreateSubscription;
use App\Jobs\DeleteSubscription;
use App\Jobs\ListSubscriptions;
use App\Jobs\ShowSubscription;
use App\Jobs\UpdateSubscription;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        ListSubscriptions::dispatch();

        return Inertia::render('subscription.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function store(SubscriptionStoreRequest $request): RedirectResponse
    {
        CreateSubscription::dispatch($request);

        return redirect()->route('subscription.index');
    }

    public function show(Request $request, Subscription $subscription)
    {
        ShowSubscription::dispatch($id);

        return Inertia::render('subscription.show', [
            'subscription' => $subscription,
        ]);
    }

    public function update(SubscriptionUpdateRequest $request, Subscription $subscription): RedirectResponse
    {
        UpdateSubscription::dispatch($request, $id);

        return redirect()->route('subscription.show', ['subscription' => $subscription]);
    }

    public function destroy(Request $request, Subscription $subscription): RedirectResponse
    {
        DeleteSubscription::dispatch($id);

        return redirect()->route('subscription.index');
    }
}
