<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Basic',
                'component_name' => 'Basic',
                'description' => 'Carta editorial cálida. Tipografía Fraunces sobre papel crema, leader dots clásicos, tacto de carta impresa de restaurante cuidado.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'warm',
                    'font_style' => 'serif',
                    'layout' => 'single-column',
                    'vibe' => 'editorial',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Modern',
                'component_name' => 'TemplateModern',
                'description' => 'Contemporáneo oscuro. Cards con imagen grande, tipografía Syne condensada, acentos amarillo cadmio con glow. Ideal para bares de autor y neo-bistros.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'dark',
                    'font_style' => 'display',
                    'layout' => 'cards-grid',
                    'vibe' => 'contemporary',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Minimalist',
                'component_name' => 'TemplateMinimalist',
                'description' => 'Blanco refinado. Cormorant Garamond, sin imágenes, columna estrecha, capítulos en numerales romanos. Tacto de menú degustación gastronómico.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'light',
                    'font_style' => 'serif',
                    'layout' => 'narrow-column',
                    'vibe' => 'gastronomic',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Trattoria',
                'component_name' => 'TemplateTrattoria',
                'description' => 'Italiano rústico. Libre Bodoni + Lora, crema envejecido y burdeos. Hero dividido con imagen enmarcada, ornamentos vegetales. Ideal para pizzerías, osterias y trattorias.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'warm',
                    'font_style' => 'display-serif',
                    'layout' => 'split-hero',
                    'vibe' => 'italian-rustic',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Neon',
                'component_name' => 'TemplateNeon',
                'description' => 'Izakaya oscuro. Big Shoulders + JetBrains Mono, negro puro con neón rosa y cian. Scanlines, corners cortados, glow tipográfico. Ideal para ramen, sushi bars y street food asiático.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'dark-neon',
                    'font_style' => 'condensed-display',
                    'layout' => 'cards-grid',
                    'vibe' => 'izakaya',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Botanica',
                'component_name' => 'TemplateBotanica',
                'description' => 'Natural y vegano. Lora italic + Nunito, crema + salvia + terracota, ornamentos de hoja, imágenes de producto en óvalos orgánicos. Ideal para restaurantes vegetarianos, bowls y bistros saludables.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'earthy',
                    'font_style' => 'serif-italic',
                    'layout' => 'card-rows',
                    'vibe' => 'organic',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Riviera',
                'component_name' => 'TemplateRiviera',
                'description' => 'Mediterráneo costero. Yeseva One + DM Sans, azul pastel con acentos dorados, ondas SVG y sol ornamental. Ideal para chiringuitos, marisquerías y bistros de playa.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'coastal',
                    'font_style' => 'display-serif',
                    'layout' => 'card-rows',
                    'vibe' => 'mediterranean',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Chapter',
                'component_name' => 'TemplateChapter',
                'description' => 'Menú degustación de autor. Bodoni Moda + Lora, marfil con oro, secciones como capítulos numerados romanos, platos numerados 01-N. Ideal para alta cocina, menús cerrados y omakase.',
                'preview_image_url' => null,
                'config' => [
                    'color_scheme' => 'refined',
                    'font_style' => 'serif',
                    'layout' => 'chapter-narrow',
                    'vibe' => 'tasting-menu',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['component_name' => $template['component_name']],
                $template,
            );
        }
    }
}
