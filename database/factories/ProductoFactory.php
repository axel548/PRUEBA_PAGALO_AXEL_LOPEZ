<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Producto;

class ProductoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nombre_de_producto' => fake()->word(),
            'precio' => fake()->randomFloat(2, 0, 9999.99),
            'stock' => fake()->numberBetween(0, 1000),
        ];
    }
}
