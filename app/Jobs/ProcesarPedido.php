<?php

namespace App\Jobs;

use App\Mail\PedidoCanceladoMail;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcesarPedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 3; // Reintentos
    public $timeout = 120; // Tiempo de ejecución máximo
    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $pedidos = Pedido::where('estado', 'pendiente')->limit(100)->get();

        foreach ($pedidos as $pedido) {
            try {
                DB::transaction(function () use ($pedido) {
                    $productospedidos = ProductoPedido::where('pedido_id', $pedido->id)->get();

                    foreach ($productospedidos as $productopedido) {
                        $producto = Producto::find($productopedido->producto_id);

                        if (!$producto || $producto->stock < $productopedido->cantidad) {
                            throw new \Exception("Stock insuficiente para el pedido ID {$pedido->id}");
                        }

                        // Reducimos el stock solo si hay suficiente
                        $producto->stock -= $productopedido->cantidad;
                        $producto->save();
                    }

                    $pedido->estado = 'procesando';
                    $pedido->save();
                });
            } catch (\Exception $e) {
                $pedido->estado = 'cancelado';
                $pedido->save();

                Mail::to($pedido->cliente->correo)->send(new PedidoCanceladoMail($pedido));

                \Log::error("Pedido ID {$pedido->id} cancelado: {$e->getMessage()}");
            }
        }
    }
}
