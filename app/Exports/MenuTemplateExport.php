<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MenuTemplateExport implements FromArray, WithColumnWidths, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['Sección', 'Producto', 'Descripción', 'Precio', 'Alérgenos', 'Ingredientes', 'Calorías'];
    }

    public function array(): array
    {
        return [
            ['Entrantes', 'Ensalada César', 'Lechuga romana, pollo, parmesano y salsa César', 12.50, 'gluten, dairy, eggs', 'lechuga, pollo, parmesano, salsa césar', 350],
            ['Entrantes', 'Croquetas de jamón', 'Croquetas caseras de jamón ibérico (6 uds)', 9.00, 'gluten, dairy, eggs', 'jamón ibérico, bechamel, pan rallado', 420],
            ['Principales', 'Solomillo a la plancha', 'Solomillo de ternera con guarnición de verduras', 22.00, '', 'solomillo de ternera, verduras de temporada', 580],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D9488'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 45,
            'D' => 12,
            'E' => 35,
            'F' => 40,
            'G' => 12,
        ];
    }
}
