<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\InventoryItem;
use App\Imports\GenericImport;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ImportStockJob;

class StockImportService
{
    protected $requiredFields = ['model', 'sale_price'];
    
    protected $dbFields = [
        'brand' => 'Marca',
        'model' => 'Modelo',
        'imei' => 'IMEI',
        'storage' => 'Almacenamiento/Capacidad',
        'color' => 'Color',
        'purchase_price' => 'Precio de Compra',
        'sale_price' => 'Precio de Venta',
        'cosmetic_condition' => 'Condición Estética',
        'serial_number' => 'Número de Serie',
        'battery_health' => 'Salud de Batería',
        'notes' => 'Notas/Comentarios',
    ];

    public function getDbFields()
    {
        return $this->dbFields;
    }

    /**
     * Parse the file and return headers and sample data
     */
    public function parseFile($filePath, $disk = 'local')
    {
        $path = Storage::disk($disk)->path($filePath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'txt' || $extension === 'csv') {
            return $this->parseCsv($path);
        }

        $data = Excel::toArray(new GenericImport, $path);
        $rows = $data[0] ?? [];
        
        if (empty($rows)) {
            return ['headers' => [], 'sample' => []];
        }

        $headers = array_shift($rows);
        $normalizedHeaders = $this->normalizeHeaders($headers);

        return [
            'headers' => $headers,
            'normalized_headers' => $normalizedHeaders,
            'sample' => array_slice($rows, 0, 5)
        ];
    }

    /**
     * Parse raw text input
     */
    public function parseRawText($text)
    {
        $separator = $this->detectSeparator($text);
        $lines = explode("\n", str_replace("\r", "", trim($text)));
        $rows = [];
        
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line, $separator);
        }

        if (empty($rows)) {
            return ['headers' => [], 'sample' => []];
        }

        $headers = array_shift($rows);
        $normalizedHeaders = $this->normalizeHeaders($headers);

        return [
            'headers' => $headers,
            'normalized_headers' => $normalizedHeaders,
            'sample' => array_slice($rows, 0, 5),
            'all_rows' => $rows
        ];
    }

    protected function normalizeHeaders($headers)
    {
        return array_map(function ($header) {
            return Str::slug(strtolower(trim($header)), '_');
        }, $headers);
    }

    protected function detectSeparator($text)
    {
        $separators = [",", ";", "\t", "|"];
        $firstLine = strtok($text, "\n");
        
        $counts = [];
        foreach ($separators as $sep) {
            $counts[$sep] = substr_count($firstLine, $sep);
        }

        arsort($counts);
        return key($counts);
    }

    protected function parseCsv($path)
    {
        $content = file_get_contents($path);
        return $this->parseRawText($content);
    }

    /**
     * Validate and prepare data for preview
     */
    public function preparePreview($filePath, $mapping, $disk = 'local')
    {
        $path = Storage::disk($disk)->path($filePath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'txt' || $extension === 'csv') {
            $content = file_get_contents($path);
            $parsed = $this->parseRawText($content);
            $rows = $parsed['all_rows'];
            $headers = $parsed['headers'];
        } else {
            $data = Excel::toArray(new GenericImport, $path);
            $rows = $data[0] ?? [];
            $headers = array_shift($rows);
        }

        $previewData = [];
        $summary = [
            'total' => count($rows),
            'valid' => 0,
            'invalid' => 0,
        ];

        foreach ($rows as $index => $row) {
            $mappedRow = [];
            foreach ($mapping as $dbField => $fileHeaderIndex) {
                if ($fileHeaderIndex !== null && isset($row[$fileHeaderIndex])) {
                    $mappedRow[$dbField] = trim($row[$fileHeaderIndex]);
                } else {
                    $mappedRow[$dbField] = null;
                }
            }

            $validation = $this->validateRow($mappedRow);
            
            if ($validation->fails()) {
                $summary['invalid']++;
                $errors = $validation->errors()->all();
            } else {
                $summary['valid']++;
                $errors = [];
            }

            $previewData[] = [
                'row_index' => $index + 2, // +2 because 1-based and header is 1
                'data' => $mappedRow,
                'is_valid' => empty($errors),
                'errors' => $errors
            ];
        }

        return [
            'preview' => $previewData,
            'summary' => $summary
        ];
    }

    /**
     * Perform the actual import
     */
    public function performImport($filePath, $mapping, $organizationId, $branchId = null, $disk = 'local')
    {
        $path = Storage::disk($disk)->path($filePath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'txt' || $extension === 'csv') {
            $content = file_get_contents($path);
            $parsed = $this->parseRawText($content);
            $rows = $parsed['all_rows'];
        } else {
            $data = Excel::toArray(new GenericImport, $path);
            $rows = $data[0] ?? [];
            array_shift($rows); // Header
        }

        $itemsToInsert = [];
        $summary = [
            'total' => count($rows),
            'success' => 0,
            'failed' => 0,
        ];

        foreach ($rows as $row) {
            $mappedRow = [];
            foreach ($mapping as $dbField => $fileHeaderIndex) {
                if ($fileHeaderIndex !== null && isset($row[$fileHeaderIndex])) {
                    $mappedRow[$dbField] = trim($row[$fileHeaderIndex]);
                } else {
                    $mappedRow[$dbField] = null;
                }
            }

            $validation = $this->validateRow($mappedRow);
            
            if ($validation->fails()) {
                $summary['failed']++;
                continue;
            }

            $brand = $mappedRow['brand'] ?? 'Apple';
            $category = strtolower($brand) === 'apple' ? 'Apple' : 'Smartphone';

            $itemsToInsert[] = [
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'category' => $category,
                'brand' => $brand,
                'model' => $mappedRow['model'],
                'storage' => $mappedRow['storage'] ?? 'N/A',
                'color' => $mappedRow['color'] ?? 'N/A',
                'imei' => $mappedRow['imei'] ?? null,
                'serial_number' => $mappedRow['serial_number'] ?? null,
                'battery_health' => $mappedRow['battery_health'] ?? 100,
                'cosmetic_condition' => $mappedRow['cosmetic_condition'] ?? 'Como Nuevo',
                'purchase_price' => $mappedRow['purchase_price'] ?? 0,
                'sale_price' => $mappedRow['sale_price'],
                'status' => 'in_stock',
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($itemsToInsert) >= 100) {
                InventoryItem::insert($itemsToInsert);
                $summary['success'] += count($itemsToInsert);
                $itemsToInsert = [];
            }
        }

        if (!empty($itemsToInsert)) {
            InventoryItem::insert($itemsToInsert);
            $summary['success'] += count($itemsToInsert);
        }

        // Cleanup
        Storage::disk($disk)->delete($filePath);

        return $summary;
    }

    protected function validateRow($data)
    {
        return Validator::make($data, [
            'model' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'imei' => 'nullable|string|regex:/^[0-9]{14,16}$/',
            'brand' => 'nullable|string|max:100',
            'storage' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'battery_health' => 'nullable|integer|between:0,100',
        ]);
    }
}
