<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\QrCode\GenerateQrCode;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Country;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Section;
use App\Models\Template;
use Database\Seeders\UeAllergenSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(): Response
    {
        $tenant = tenant();

        // Map legacy steps: old 0=website, 1=location, 2=menu, 3=products, 4=complete
        // New steps:        0=basics, 1=template, 2=sections+products, 3=complete
        $rawStep = (int) ($tenant->onboarding_step ?? 0);
        $step = $this->mapLegacyStep($rawStep);

        $location = Location::orderBy('id')->first();
        $menu = $location ? Menu::where('location_id', $location->id)->orderBy('id')->first() : null;
        $products = $menu ? Product::whereHas('sections', function ($q) use ($menu) {
            $q->where('menu_id', $menu->id);
        })->get() : collect();

        $templates = Template::where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'component_name', 'description', 'preview_image_url', 'config']);

        return Inertia::render('admin/tenant/onboarding/Wizard', [
            'step'       => $step,
            'tenantName' => $tenant->name ?? ucfirst($tenant->id),
            'userName'   => auth()->user()?->name ?? '',
            'location'   => $location,
            'menu'       => $menu,
            'products'   => $products,
            'templates'  => $templates,
        ]);
    }

    /**
     * Map old step numbers to new ones for backwards-compat.
     * Old: 0=website, 1=location, 2=menu, 3=products, 4=complete
     * New: 0=basics,  1=template, 2=products,          3=complete
     */
    protected function mapLegacyStep(int $raw): int
    {
        return match ($raw) {
            0       => 0,   // website → basics
            1       => 0,   // location → basics (redo if not created yet)
            2       => 1,   // menu → template
            3       => 2,   // products → products
            default => 3,   // 4+ → complete
        };
    }

    /**
     * Step 1 — Basics: persist city + phone, create the Location from tenant name.
     */
    public function storeBasics(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'city'  => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant = tenant();
        $locationName = $tenant->name ?? ucfirst($tenant->id);
        $slug = Str::slug($locationName) ?: $tenant->id;

        $country = Country::firstOrCreate(
            ['code' => 'ES'],
            ['name' => 'España']
        );

        // Create only if not yet created
        if (! Location::where('tenant_id', $tenant->id)->exists()) {
            Location::create([
                'name'        => $locationName,
                'address'     => '',
                'city'        => $data['city'] ?? '',
                'phone'       => $data['phone'] ?? null,
                'province'    => '',
                'postal_code' => '',
                'country_id'  => $country->id,
                'user_id'     => auth()->id(),
                'tenant_id'   => $tenant->id,
                'slug'        => $slug,
                'url'         => $slug,
                'currency'    => 'EUR',
                'time_zone'   => 'Europe/Madrid',
                'time_format' => 'H:i',
                'lang'        => 'es',
                'languages'   => ['es'],
                'social_medias' => [],
            ]);
        } else {
            // Update city/phone on existing location
            $location = Location::where('tenant_id', $tenant->id)->first();
            $location->city  = $data['city']  ?? $location->city;
            $location->phone = $data['phone'] ?? $location->phone;
            $location->save();
        }

        // Seed 14 EU allergens (idempotent)
        try {
            UeAllergenSeeder::seedForTenant($tenant->id);
        } catch (\Throwable $e) {
            Log::error('UeAllergenSeeder failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
        }

        $tenant->onboarding_step = 2; // maps to new step 1 (template)
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    /**
     * Step 2 — Template: create Menu with selected template_id.
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:templates,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        $tenant = tenant();
        $menuName = $tenant->name ?? ucfirst($tenant->id);

        // Create menu only if not yet created for this location
        if (! Menu::where('location_id', $data['location_id'])->exists()) {
            Menu::create([
                'name'          => $menuName,
                'description'   => null,
                'location_id'   => (int) $data['location_id'],
                'template_id'   => (int) $data['template_id'],
                'is_active'     => true,
                'lang'          => config('menulinker.source_locale', 'es'),
                'show_prices'   => true,
                'show_currency' => false,
                'show_calories' => false,
            ]);
        } else {
            // Update template on existing menu
            $menu = Menu::where('location_id', $data['location_id'])->first();
            $menu->template_id = (int) $data['template_id'];
            $menu->save();
        }

        $tenant->onboarding_step = 3; // maps to new step 2 (products)
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    /**
     * Step 3 — Products: create section + products. Can be called multiple times.
     */
    public function storeProducts(Request $request): RedirectResponse
    {
        $request->validate([
            'products'               => ['required', 'array', 'min:1'],
            'products.*.name'        => ['required', 'string'],
            'products.*.price'       => ['required', 'numeric', 'min:0'],
            'products.*.section_name' => ['required', 'string'],
            'menu_id'                => ['required', 'integer', 'exists:menus,id'],
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
                    'name'      => $item['name'],
                    'price'     => $item['price'],
                    'tenant_id' => $tenant->id,
                ]);

                DB::table('product_section')->insert([
                    'product_id' => $product->id,
                    'section_id' => $section->id,
                    'tenant_id'  => $tenant->id,
                ]);
            }
        }

        $tenant->onboarding_step = 4; // maps to new step 3 (complete)
        $tenant->save();

        return redirect()->route('tenant.onboarding.show');
    }

    /**
     * Final step — generate QR, send welcome mail, mark onboarding done.
     */
    public function complete(Request $request): RedirectResponse
    {
        $request->validate([
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ]);

        $menu = Menu::findOrFail((int) $request->input('menu_id'));

        $qr = (new GenerateQrCode)->execute($menu);

        $tenant = tenant();
        $tenant->onboarding_completed_at = now();
        $tenant->save();

        $menuUrl = $qr->url;
        $user = auth()->user();
        if ($user) {
            try {
                $qrDownloadUrl = $qr->image_url
                    ? Storage::disk('public')->url($qr->image_url)
                    : $menuUrl;

                Mail::to($user)->send(new WelcomeMail($user, $menuUrl, $qrDownloadUrl));
            } catch (\Throwable $e) {
                Log::error('WelcomeMail failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $location = Location::orderBy('id')->first();

        if ($location) {
            return redirect()->route('tenant.locations.show', $location)
                ->with('welcome_onboarding', true)
                ->with('menu_public_url', $menuUrl);
        }

        return redirect()->route('tenant.dashboard')
            ->with('welcome_onboarding', true)
            ->with('menu_public_url', $menuUrl);
    }
}
