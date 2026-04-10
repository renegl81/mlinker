<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteSettingsController extends Controller
{
    public function show(): Response
    {
        $tenant = tenant();

        return Inertia::render('settings/Website', [
            'hasWebsite'   => (bool) $tenant->has_website,
            'businessType' => $tenant->business_type ?? 'restaurant',
            'homeTemplate' => $tenant->home_template ?? 'HomeClassic',
            'businessTypes' => config('menulinker.business_types', []),
            'homeTemplates' => config('menulinker.home_templates', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'has_website'   => ['required', 'boolean'],
            'business_type' => ['required', 'string', 'in:restaurant,cafe,bar,fastfood,finedining'],
            'home_template' => ['required', 'string', 'in:HomeClassic,HomeCafe,HomeBar,HomeFastfood,HomeFineDining'],
        ]);

        $tenant = tenant();
        $tenant->has_website   = (bool) $data['has_website'];
        $tenant->business_type = $data['business_type'];
        $tenant->home_template = $data['home_template'];
        $tenant->save();

        return back()->with('success', 'Configuración de web pública guardada.');
    }
}
