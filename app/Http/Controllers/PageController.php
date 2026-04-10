<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function faq(): Response
    {
        return Inertia::render('Faq', [
            'seo' => [
                'title' => 'Preguntas frecuentes — MenuLinker',
                'description' => 'Resuelve tus dudas sobre MenuLinker: precios, funcionamiento, idiomas, alérgenos, plantillas, soporte y más.',
                'url' => config('app.url').'/faq',
            ],
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Contact', [
            'seo' => [
                'title' => 'Contacto — MenuLinker',
                'description' => 'Ponte en contacto con el equipo de MenuLinker. Estamos aquí para ayudarte con soporte técnico, consultas comerciales y todo lo que necesites.',
                'url' => config('app.url').'/contact',
            ],
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Privacy', [
            'seo' => [
                'title' => 'Política de privacidad — MenuLinker',
                'description' => 'Política de privacidad de MenuLinker. Información sobre tratamiento de datos personales conforme al RGPD.',
                'url' => config('app.url').'/privacy',
            ],
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Terms', [
            'seo' => [
                'title' => 'Términos y condiciones — MenuLinker',
                'description' => 'Condiciones generales de uso y contratación de MenuLinker.',
                'url' => config('app.url').'/terms',
            ],
        ]);
    }

    public function cookies(): Response
    {
        return Inertia::render('Cookies', [
            'seo' => [
                'title' => 'Política de cookies — MenuLinker',
                'description' => 'Información sobre el uso de cookies en MenuLinker y cómo gestionarlas.',
                'url' => config('app.url').'/cookies',
            ],
        ]);
    }
}
