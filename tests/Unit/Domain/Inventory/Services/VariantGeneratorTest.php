<?php

namespace Tests\Unit\Domain\Inventory\Services;

use App\Domain\Inventory\Services\VariantGenerator;
use PHPUnit\Framework\TestCase;

class VariantGeneratorTest extends TestCase
{
    public function test_it_generates_combinations_correctly()
    {
        $generator = new VariantGenerator();
        $attributes = [
            'Color' => ['Red', 'Blue'],
            'Size' => ['S', 'M', 'L'],
        ];

        $variants = $generator->generate($attributes);

        $this->assertCount(6, $variants); // 2 * 3 = 6
        $this->assertEquals(['Color' => 'Red', 'Size' => 'S'], $variants->first());
        $this->assertEquals(['Color' => 'Blue', 'Size' => 'L'], $variants->last());
    }

    public function test_it_generates_sku_correctly()
    {
        $generator = new VariantGenerator();
        $baseSku = 'PROD-001';
        $attributes = ['Color' => 'Red', 'Size' => 'Large'];

        $sku = $generator->generateSku($baseSku, $attributes);

        $this->assertEquals('PROD-001-RED-LARGE', $sku);
    }
}
