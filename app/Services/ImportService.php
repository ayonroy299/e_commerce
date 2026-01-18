<?php

namespace App\Services;

use App\Models\Import;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportService
{
    /**
     * Start the import process for a given file and type.
     */
    public function import(int $userId, ?int $branchId, string $type, string $filePath)
    {
        $import = Import::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'type' => $type,
            'file_path' => $filePath,
            'status' => 'processing',
        ]);

        try {
            switch ($type) {
                case 'products':
                    $this->importProducts($import);
                    break;
                case 'customers':
                    $this->importCustomers($import);
                    break;
                case 'suppliers':
                    $this->importSuppliers($import);
                    break;
                default:
                    throw new \Exception("Unsupported import type: {$type}");
            }

            $import->update(['status' => 'completed']);
        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'errors_json' => ['message' => $e->getMessage()],
            ]);
        }

        return $import;
    }

    protected function importProducts(Import $import)
    {
        $path = Storage::path($import->file_path);
        
        // Simple CSV parser for demonstration, 
        // In a real scenario, use Laravel-Excel for better handling
        if (($handle = fopen($path, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            $rows = [];
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = array_combine($header, $data);
            }
            fclose($handle);

            $import->update(['total_rows' => count($rows)]);

            foreach ($rows as $row) {
                // Simplified product creation
                Product::updateOrCreate(
                    ['name' => $row['name'], 'branch_id' => $import->branch_id],
                    [
                        'description' => $row['description'] ?? null,
                        'category_id' => $row['category_id'] ?? null,
                        'brand_id' => $row['brand_id'] ?? null,
                        'tax_id' => $row['tax_id'] ?? null,
                        'unit_id' => $row['unit_id'] ?? null,
                        'is_active' => true,
                    ]
                );
                
                $import->increment('processed_rows');
            }
        }
    }

    protected function importCustomers(Import $import)
    {
        // Similar logic to importProducts
    }

    protected function importSuppliers(Import $import)
    {
        // Similar logic to importProducts
    }
}
