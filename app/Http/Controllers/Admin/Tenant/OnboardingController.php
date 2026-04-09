<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\QrCode\GenerateQrCode;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Section;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(): Response
    {
        $tenant = tenant();
        $step = (int) ($tenant->onboarding_step ?? 0);

        $location = Location::orderBy('id')->first();
        $menu = $location ? Menu::where('location_id', $location->id)->orderBy('id')->first() : null;
        $products = $menu ? Product::whereHas('sections', function ($q) use ($menu) {
            $q->where('menu_id', $menu->id);
        })->get() : collect();

        return Inertia::render('admin/tenant/onboarding/Wizard', [
            'step' => $step,
            'location' => $location,
            'menu' => $menu,
            'products' => $products,
        ]);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
        ]);

        $tenant = tenant();
        $slug = Str::slug($data['name']);

        $country = Country::firstOrCreate(
            ['code' => 'ES'],
            ['name' => 'España']
        );

        Location::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'phone' => $data['phone'] ?? null,
            'province' => '',
            'postal_code' => '',
            'country_id' => $country->id,
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'slug' => $slug,
            'url' => $slug,
            'currency' => 'EUR',
            'time_zone' => 'Europe/Madrid',
            'time_format' => 'H:i',
            'lang' => 'es',
            'languages' => ['es'],
            'social_medias' => [],
        ]);

        $tenant->onboarding_step = 1;
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    public function storeMenu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        $template = Template::where('component_name', 'Basic')->first()
            ?? Template::where('is_active', true)->first()
            ?? Template::first();

        Menu::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location_id' => (int) $data['location_id'],
            'template_id' => $template?->id,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => false,
            'show_calories' => false,
        ]);

        $tenant = tenant();
        $tenant->onboarding_step = 2;
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    public function storeProducts(Request $request): RedirectResponse
    {
        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.name' => ['required', 'string'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.section_name' => ['required', 'string'],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ]);

        $tenant = tenant();
        $menuId = (int) $request->input('menu_id');

        $grouped = collect($request->input('products'))->groupBy('section_name');

        foreach ($grouped as $sectionName => $items) {
            $section = Section::firstOrCreate(
                ['menu_id' => $menuId, 'name' => $sectionName],
                ['tenant_id' => $tenant->id],
            );

            foreach ($items as $item) {
                $product = Product::create([
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'tenant_id' => $tenant->id,
                ]);

                DB::table('product_section')->insert([
                    'product_id' => $product->id,
                    'section_id' => $section->id,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        $tenant->onboarding_step = 3;
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->validate([
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ]);

        $menu = Menu::findOrFail((int) $request->input('menu_id'));

        (new GenerateQrCode)->execute($menu);

        $tenant = tenant();
        $tenant->onboarding_completed_at = now();
        $tenant->save();

        return redirect()->route('tenant.dashboard')
            ->with('success', '¡Tu menú está listo!');
    }
}
