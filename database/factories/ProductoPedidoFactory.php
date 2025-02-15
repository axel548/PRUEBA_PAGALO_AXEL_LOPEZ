<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPedido;

class ProductoPedidoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductoPedido::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'producto_id' => Producto::factory(),
            'precio' => fake()->randomFloat(2, 0, 999999.99),
            'cantidad' => fake()->numberBetween(-10000, 10000),
            'descuento' => fake()->randomFloat(2, 0, 999.99),
        ];
    }
}
