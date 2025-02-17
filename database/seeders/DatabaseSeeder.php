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
        Cliente::factory(5000)->create();
        Producto::factory(500)->create();

        $this->createPedidos(500);

        // aproximadamente 30 min de espera para generar 250,000 pedidos
        // for ($i=0; $i < 100; $i++) {
        //     $this->createPedidos(2500);
        // }

    }

    private function createPedidos($cantidad_pedidos_generar = 2500) {
        $clientes = Cliente::inRandomOrder()->limit($cantidad_pedidos_generar)->get();

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
