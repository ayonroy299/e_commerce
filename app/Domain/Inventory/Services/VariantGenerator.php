<?php

namespace App\Domain\Inventory\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VariantGenerator
{
    /**
     * Generate combinations from a list of attributes and their values.
     *
     * @param array $attributes Example: ['Color' => ['Red', 'Blue'], 'Size' => ['S', 'M']]
     * @return Collection
     */
    public function generate(array $attributes): Collection
    {
        if (empty($attributes)) {
            return collect([]);
        }

        $names = array_keys($attributes);
        $values = array_values($attributes);

        $combinations = $this->cartesian($values);

        return collect($combinations)->map(function ($combination) use ($names) {
            $variant = [];
            foreach ($combination as $index => $value) {
                $variant[$names[$index]] = $value;
            }
            return $variant;
        });
    }

    /**
     * Calculate Cartesian product of input arrays.
     *
     * @param array $input
     * @return array
     */
    protected function cartesian(array $input): array
    {
        $result = [[]];

        foreach ($input as $key => $values) {
            $append = [];

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    /**
     * Generate a SKU from a product SKU and variant attributes.
     *
     * @param string $productSku
     * @param array $attributes
     * @return string
     */
    public function generateSku(string $productSku, array $attributes): string
    {
        $suffix = collect($attributes)
            ->values()
            ->map(fn($val) => Str::upper(Str::slug($val)))
            ->join('-');

        return "{$productSku}-{$suffix}";
    }
}
