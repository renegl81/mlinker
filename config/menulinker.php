<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    |
    | Locales available for menu content translation. The first entry is
    | considered the source locale used to generate all translations.
    */
    'source_locale' => 'es',

    'supported_locales' => [
        'es' => ['native' => 'Español', 'flag' => '🇪🇸'],
        'en' => ['native' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷'],
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪'],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹'],
        'pt' => ['native' => 'Português', 'flag' => '🇵🇹'],
        'ca' => ['native' => 'Català / Valencià', 'flag' => 'ca'],
        'gl' => ['native' => 'Galego', 'flag' => 'gl'],
        'eu' => ['native' => 'Euskara', 'flag' => 'eu'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Types
    |--------------------------------------------------------------------------
    */
    'business_types' => [
        'restaurant' => 'Restaurante',
        'cafe'       => 'Cafetería',
        'bar'        => 'Bar / Pub',
        'fastfood'   => 'Fast Food / Street Food',
        'finedining' => 'Alta Cocina',
    ],

    /*
    |--------------------------------------------------------------------------
    | Home Templates
    |--------------------------------------------------------------------------
    */
    'home_templates' => [
        'HomeClassic' => [
            'name'           => 'Clásico',
            'business_types' => ['restaurant'],
            'description'    => 'Hero full-width, horarios en grid, carta como cards. Serif elegante.',
        ],
        'HomeCafe' => [
            'name'           => 'Cafetería',
            'business_types' => ['cafe'],
            'description'    => 'Estilo acogedor, imagen circular, redes sociales prominentes.',
        ],
        'HomeBar' => [
            'name'           => 'Bar',
            'business_types' => ['bar'],
            'description'    => 'Fondo oscuro, tipografía bold, gradiente de neón sutil.',
        ],
        'HomeFastfood' => [
            'name'           => 'Street Food',
            'business_types' => ['fastfood'],
            'description'    => 'Colores vivos, hero geométrico, CTA táctil prominente.',
        ],
        'HomeFineDining' => [
            'name'           => 'Alta Cocina',
            'business_types' => ['finedining', 'restaurant'],
            'description'    => 'Ultra minimal, serif fina, imagen a pantalla completa.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default home template per business type
    |--------------------------------------------------------------------------
    */
    'default_home_template' => [
        'restaurant' => 'HomeClassic',
        'cafe'       => 'HomeCafe',
        'bar'        => 'HomeBar',
        'fastfood'   => 'HomeFastfood',
        'finedining' => 'HomeFineDining',
    ],
];
