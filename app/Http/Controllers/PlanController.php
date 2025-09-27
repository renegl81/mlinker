<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanStoreRequest;
use App\Http\Requests\PlanUpdateRequest;
use App\Jobs\CreatePlan;
use App\Jobs\DeletePlan;
use App\Jobs\ListPlans;
use App\Jobs\ShowPlan;
use App\Jobs\UpdatePlan;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        ListPlans::dispatch();

        return Inertia::render('plan.index', [
            'plans' => $plans,
        ]);
    }

    public function store(PlanStoreRequest $request): RedirectResponse
    {
        CreatePlan::dispatch($request);

        return redirect()->route('plan.index');
    }

    public function show(Request $request, Plan $plan)
    {
        ShowPlan::dispatch($id);

        return Inertia::render('plan.show', [
            'plan' => $plan,
        ]);
    }

    public function update(PlanUpdateRequest $request, Plan $plan): RedirectResponse
    {
        UpdatePlan::dispatch($request, $id);

        return redirect()->route('plan.show', ['plan' => $plan]);
    }

    public function destroy(Request $request, Plan $plan): RedirectResponse
    {
        DeletePlan::dispatch($id);

        return redirect()->route('plan.index');
    }
}
