<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        // Si hay tenant inicializado, mostrar vista del tenant
        if (tenancy()->initialized) {
            return Inertia::render('tenant/Home', [
                'tenant' => tenant(),
            ]);
        }

        return Inertia::render('Home', [

        ]);
    }
}
