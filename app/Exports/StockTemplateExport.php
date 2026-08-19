<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StockTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function array(): array
    {
        return [
            ['Apple', 'iPhone 13', '256GB', 'Midnight', '90', 'Como Nuevo', '', '', '700', '850', 'Sin caja']
        ];
    }

    public function headings(): array
    {
        return [
            'Marca',
            'Modelo',
            'Almacenamiento/Capacidad',
            'Color',
            'Salud de Batería',
            'Condición Estética',
            'IMEI',
            'Número de Serie',
            'Precio de Compra',
            'Precio de Venta',
            'Notas/Comentarios',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
