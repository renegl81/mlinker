<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        // Sin tenant inicializado → home central de MenuLinker
        if (! tenancy()->initialized) {
            return Inertia::render('Home');
        }

        $tenant = tenant();

        // Si el tenant no quiere web pública → redirect al primer menú activo o 404
        if (! $tenant->has_website) {
            $firstMenu = Menu::where('is_active', true)->first();
            if ($firstMenu) {
                return redirect("/menu/{$firstMenu->id}");
            }

            abort(404);
        }

        $locations = Location::with([
            'menus' => fn ($q) => $q->where('is_active', true),
            'openingHours',
        ])
            ->orderBy('name')
            ->get();

        $isMultiLocation = $locations->count() > 1;
        $primaryLocation = $locations->first();
        $template = $tenant->home_template ?? 'HomeClassic';

        return Inertia::render("tenant/home/{$template}", [
            'tenant' => [
                'id'           => $tenant->id,
                'name'         => $tenant->id,
                'businessType' => $tenant->business_type ?? 'restaurant',
            ],
            'locations'       => $locations,
            'primaryLocation' => $primaryLocation,
            'isMultiLocation' => $isMultiLocation,
            'seo'             => $this->buildSeo($tenant, $primaryLocation),
        ]);
    }

    private function buildSeo(mixed $tenant, mixed $primaryLocation): array
    {
        $siteName = $primaryLocation?->name ?? ucfirst((string) $tenant->id);

        if ($primaryLocation && $primaryLocation->description) {
            $description = $primaryLocation->description;
        } else {
            $description = "Bienvenido a {$siteName}. Consulta nuestra carta digital, horarios y contacto.";
        }

        $title = $primaryLocation
            ? "{$primaryLocation->name} — Carta digital"
            : "{$siteName} — Nuestros locales";

        $image = $primaryLocation?->image_url ?? $primaryLocation?->logo_url ?? null;

        $url = request()->url();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'Restaurant',
            'name'     => $siteName,
            'url'      => $url,
        ];

        if ($primaryLocation) {
            $jsonLd['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $primaryLocation->address ?? '',
                'addressLocality' => $primaryLocation->city ?? '',
            ];

            if ($primaryLocation->phone) {
                $jsonLd['telephone'] = $primaryLocation->phone;
            }

            if ($image) {
                $jsonLd['image'] = $image;
            }

            // Opening hours spec
            if ($primaryLocation->openingHours && $primaryLocation->openingHours->isNotEmpty()) {
                $days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                $hoursSpec = [];
                foreach ($primaryLocation->openingHours as $oh) {
                    if (! $oh->is_closed) {
                        $hoursSpec[] = [
                            '@type'     => 'OpeningHoursSpecification',
                            'dayOfWeek' => "https://schema.org/{$days[$oh->weekday]}",
                            'opens'     => $oh->opens_at,
                            'closes'    => $oh->closes_at,
                        ];
                    }
                }
                if (! empty($hoursSpec)) {
                    $jsonLd['openingHoursSpecification'] = $hoursSpec;
                }
            }
        }

        return compact('title', 'description', 'image', 'url', 'jsonLd');
    }
}
