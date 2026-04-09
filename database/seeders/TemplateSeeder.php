<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Basic',
                'component_name' => 'Basic',
                'description' => 'A timeless design with a focus on simplicity and elegance.',
                'preview_image_url' => 'https://example.com/images/templates/classic.png',
                'config' => [
                    'color_scheme' => 'light',
                    'font_style' => 'serif',
                    'layout' => 'single-column',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Modern',
                'component_name' => 'TemplateModern',
                'description' => 'A sleek and contemporary design with bold colors and dynamic layouts.',
                'preview_image_url' => 'https://example.com/images/templates/modern.png',
                'config' => [
                    'color_scheme' => 'dark',
                    'font_style' => 'sans-serif',
                    'layout' => 'multi-column',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Minimalist',
                'component_name' => 'TemplateMinimalist',
                'description' => 'A clean and simple design that emphasizes content over decoration.',
                'preview_image_url' => 'https://example.com/images/templates/minimalist.png',
                'config' => [
                    'color_scheme' => 'neutral',
                    'font_style' => 'monospace',
                    'layout' => 'grid',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }

    }
}
