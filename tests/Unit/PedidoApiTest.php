<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTProvider;

class PedidoApiTest extends TestCase
{
    use RefreshDatabase; // Esto asegura que la base de datos se reinicie en cada prueba.
    public $user;

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

    private function generateData() {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Cliente::factory(200)->create();
        Producto::factory(100)->create();

        $this->createPedidos(100);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->generateData();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_pedidos()
    {
        Pedido::factory()->count(5)->create(); // Crea pedidos de prueba

        $response = $this->actingAs($this->user, 'api')->getJson('/api/pedidos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'cliente_id', 'metodo_de_pago', 'total', 'estado', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_pedidos()
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/pedidos/filter?&estado=procesando');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'cliente_id', 'metodo_de_pago', 'total', 'estado', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_pedido_with_productos()
    {
        // Datos de prueba
        $productos = Producto::factory()->count(3)->create();


        $pedidoData = [
            'cliente_id' => Cliente::inRandomOrder()->first()->id,
            'total' => 150.00,
            "metodo_de_pago" => "tarjeta",
            'estado' => 'pendiente',
            'productos' => $productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'precio' => 50.00,
                    'cantidad' => 1,
                    'descuento' => 0,
                ];
            })->toArray(),
        ];

        // Ejecutar la petición
        $response = $this->actingAs($this->user, 'api')->postJson('/api/pedidos', $pedidoData);
        // dd($response->getContent());
        // Validar respuesta esperada
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'pedido' => ['id', 'cliente_id', 'total', 'estado', 'created_at'],
                     'status'
                 ]);
    }
}
