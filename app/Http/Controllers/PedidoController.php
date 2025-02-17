<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\ProductoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Pedido::paginate(15);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = [
            'status' => 500,
            'message' => 'Error al procesar la solicitud',
        ];

        try {
            $response = DB::transaction(function () use ($request) {
                $pedido = Pedido::create($request->all());
                foreach ($request->all()['productos'] as $producto) {
                    ProductoPedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $producto['id'],
                        'precio' => $producto['precio'],
                        'cantidad' => $producto['cantidad'],
                        'descuento' => $producto['descuento'],
                    ]);
                }
                return [
                    'pedido' => $pedido,
                    'status' => 201,
                ];
            });
        } catch (\Exception $e) {

            $response['message'] = $e->getMessage();
            $response['status'] = 400;
        }

        return response()->json($response, $response['status']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $estados =['pendiente', 'procesando', 'completado', 'cancelado'];

        $id = $request->all()['id'];
        $estado = $request->all()['estado'];
        $pedido = Pedido::findOrFail($id);

        if (!in_array($estado, $estados)) {
            return response()->json(['message' => 'Estado no válido'], 400);
        }

        if (in_array($pedido->estado, ['completado', 'cancelado'])) {
            return response()->json([
                'message' => 'No se puede cambiar el estado de un pedido ya completado o cancelado.'
            ], 400);
        }

        $pedido->estado = $estado;
        $pedido->save();

        return response()->json([
            'message' => 'Estado actualizado con éxito.',
            'pedido' => $pedido
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Cancel the specified resource from storage.
     */
    public function cancel(Request $request)
    {
        $id = $request->all()['id'];
        $message = 'No puedes cancelar un pedido con el estado: ';
        $status = 409;

        $pedido = Pedido::findOrFail($id);
        if ($pedido->estado === 'pendiente') {
            $pedido->estado = 'cancelado';
            $pedido->save();
            $message = 'Pedido cancelado';
            $status = 200;
        } else {
            $message .= $pedido->estado . '.';
        }

        return response()->json(['message' => $message], $status);
    }

    /**
     * Display a listing of the resource by filters.
     */
    public function filter(Request $request)
    {
        $query = Pedido::query();

        if ($request->has('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        return $query->paginate(15);
    }
}
