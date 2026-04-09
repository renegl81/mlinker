<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AccountActivationController extends Controller
{
    /**
     * Show the activation sent page.
     */
    public function sent(): Response
    {
        return Inertia::render('auth/ActivationSent');
    }

    /**
     * Activate the user account.
     *
     * @throws Throwable
     */
    public function activate(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, __('auth.register.activation.invalid_link'));
        }

        if ($user->is_active) {
            return to_route('login')
                ->with('error', __('auth.register.activation.already_activated'));
        }

        DB::transaction(function () use ($user) {
            $user->markAsActive();

            // Activar en la tabla pivot tenant_user
            $tenant = $user->tenants()->first();
            if ($tenant) {
                $user->tenants()->updateExistingPivot(
                    $tenant->id,
                    ['is_active' => true]
                );
            }
        });

        Auth::login($user);
        $tenant = $user->tenants()->first();
        if ($tenant && ! empty($tenant->domain)) {
            $domain = rtrim($tenant->domain, '/');
            $port = config('services.app_port') ? ':'.config('services.app_port') : '';
            $url = str_starts_with($domain, 'http') ? $domain.$port.'/panel' : 'https://'.$domain.$port.'/panel';

            return redirect()->to($url)
                ->with('success', __('auth.register.activation.success'));
        }

        return redirect()->route('login')
            ->with('success', __('auth.register.activation.success'));
    }
}
