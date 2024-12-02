<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AttributeValue;
use App\Models\ProductVariation;
use App\Models\VariationAttribute;

class VariationAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VariationAttribute::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'product_variation_id' => ProductVariation::factory(),
            'attribute_value_id' => AttributeValue::factory(),
        ];
    }
}
