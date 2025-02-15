<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cliente;
use App\Models\Pedido;

class PedidoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pedido::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'metodo_de_pago' => fake()->word(),
            'total' => fake()->randomFloat(2, 0, 999999.99),
            'estado' => fake()->randomElement(["pendiente","procesando","completado","cancelado"]),
        ];
    }
}
