<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class MediaSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_have_activity_log()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_id' => $category->id,
            'subject_type' => Category::class,
            'description' => 'created',
        ]);
    }

    public function test_brand_can_have_activity_log()
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand'
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_id' => $brand->id,
            'subject_type' => Brand::class,
            'description' => 'created',
        ]);
    }
}
