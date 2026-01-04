<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AccountActivationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * @throws Throwable
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $domainName = $validated['tenant_domain'].'.'.config('app.domain');
        // Crear Tenant (sin base de datos)
        DB::table('tenants')->insert([
            'id' => $validated['tenant_domain'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tenant = (object) ['id' => $validated['tenant_domain']];

        // Crear usuario y asociar al tenant
        DB::transaction(function () use ($validated, $tenant, $domainName) {
            $user = User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'owner',
                'is_active' => false,
            ]);
            $role = Role::where('name', 'Owner')->first();
            if ($role) {
                $user->assignRole($role);
                $user->save();
            }

            // Fallback directo a la tabla domains (evita triggers/bootstrappers)
            DB::table('domains')->insert(
                [
                    'tenant_id' => $tenant->id,
                    'domain' => $domainName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('tenant_user')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'role' => 'owner',
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
             //Artisan::call('tenants:storage-link');
            // Enviar notificación
            try{
                $user->notify(new AccountActivationNotification($user));
            }catch (\Exception $exception){
                Log::error('Error enviando notificación de activación', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }

        });

        return to_route('auth.activation.sent')
            ->with('success', __('auth.register.activation.sent'));
    }

}
