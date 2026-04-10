<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['name', 'slug', 'price', 'period', 'description', 'max_locations', 'max_menus_per_location', 'max_products', 'has_analytics', 'has_custom_qr', 'has_multilang', 'has_catalog', 'has_team', 'has_api_access', 'has_custom_domain', 'show_branding', 'trial_days']);

        return Inertia::render('Home', [
            'plans' => $plans,
            'seo' => [
                'title' => 'MenuLinker — Menús digitales con QR para restaurantes',
                'description' => 'Crea tu carta digital en minutos. Genera QR personalizados, gestiona alérgenos, traduce a múltiples idiomas y actualiza precios en tiempo real. Gratis para empezar.',
                'url' => config('app.url'),
                'image' => config('app.url').'/images/og-home.jpg',
            ],
        ]);
    }

    public function sitemap(): HttpResponse
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now = now()->toW3cString();

        $urls = [
            ['loc' => $baseUrl.'/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/login', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/register', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/doc', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$now.'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
