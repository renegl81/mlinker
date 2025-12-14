<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;

class DashboardController extends Controller
{
     public function index()
     {
        return Inertia::render('Dashboard',[

        ]);
     }
}
