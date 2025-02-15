<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPedido;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Cliente::factory(50)->create();
        Producto::factory(100)->create();


        $clientes = Cliente::inRandomOrder()->limit(rand(15, 35))->get();

        foreach ($clientes as $cliente) {
            $estado = collect(["pendiente", "procesando", "completado", "cancelado"])->random();
            $metodoPago = collect([
                "tarjeta de crédito",
                "tarjeta de débito",
                "PayPal",
                "transferencia bancaria",
                "efectivo",
                "criptomoneda",
                "Apple Pay",
                "Google Pay"
            ])->random();

            $productos = Producto::inRandomOrder()->limit(rand(1, 5))->get();

            $total = 0;

            $productosConDetalles = $productos->map(function ($producto) use (&$total) {
                $cantidad = rand(1, 3);
                $descuento = rand(0, 20);
                $precioConDescuento = $producto->precio * (1 - ($descuento / 100));
                $subtotal = $precioConDescuento * $cantidad;
                $total += $subtotal;

                return [
                    'producto_id' => $producto->id,
                    'precio' => $producto->precio,
                    'cantidad' => $cantidad,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal,
                ];
            });

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'metodo_de_pago' => $metodoPago,
                'total' => $total,
                'estado' => $estado,
            ]);

            foreach ($productosConDetalles as $detalle) {
                ProductoPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $detalle['producto_id'],
                    'precio' => $detalle['precio'],
                    'cantidad' => $detalle['cantidad'],
                    'descuento' => $detalle['descuento'],
                ]);
            }
        }

    }
}
