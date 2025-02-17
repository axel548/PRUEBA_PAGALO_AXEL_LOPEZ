<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\ProductoPedido;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

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
        $estados = ['pendiente', 'procesando', 'completado', 'cancelado'];

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

    public function reporte(Request $request)
    {
        set_time_limit(120); // 2 minutos
        $type = $request->input('type', 'json');


        $pedidos = Pedido::with('cliente:id,nombre,apellido,correo')
            ->when($request->has('fecha'), function ($query) use ($request) {
                $query->whereDate('created_at', $request->fecha);
            })
            ->when($request->has('estado'), function ($query) use ($request) {
                $query->where('estado', $request->estado);
            })
            ->when($request->has('cliente_id'), function ($query) use ($request) {
                $query->where('cliente_id', $request->cliente_id);
            })
            ->select('id', 'metodo_de_pago', 'total', 'created_at', 'estado', 'cliente_id');

        if ($request->has('paginate')) {
            $pedidos = $pedidos->paginate($request->input('paginate'));
        } else {
            $pedidos = $pedidos->get();
        }


        if ($type === 'excel') {
            $exported = $this->export($pedidos);
            return response()->json($exported['response'], $exported['status']);
        } elseif ($type === 'json') {
            return response()->json($pedidos, 200);
        } else {
            return response()->json(['message' => 'Tipo de reporte no válido'], 400);
        }
    }

    private function export($data)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getDefaultColumnDimension()->setWidth(20);
            $sheet->setCellValue('A1', 'No Pedido');
            $sheet->setCellValue('B1', 'Metodo de Pago');
            $sheet->setCellValue('C1', 'Total');
            $sheet->setCellValue('D1', 'Estado');
            $sheet->setCellValue('E1', 'Cantidad Productos');
            $sheet->setCellValue('F1', 'Descuento');
            $sheet->setCellValue('G1', 'Nombre Cliente');
            $sheet->setCellValue('H1', 'Correo Cliente');
            $sheet->setCellValue('I1', 'Creado');

            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, "PD-" . $item->id);
                $sheet->setCellValue('B' . $row, $item->metodo_de_pago);
                $sheet->setCellValue('C' . $row, $item->total);
                $sheet->setCellValue('D' . $row, $item->estado);
                $sheet->setCellValue('E' . $row, $item->totalCantidad());
                $sheet->setCellValue('F' . $row, $item->totalDescuento());
                $sheet->setCellValue('G' . $row, $item->cliente->nombre . $item->cliente->apellido);
                $sheet->setCellValue('H' . $row, $item->cliente->correo);
                $sheet->setCellValue('I' . $row, $item->created_at);

                $row++;
            }


            $fileName = 'reporte_pedidos_' . now()->format('Ymd_His') . '.xlsx';
            $filePath = "reports/$fileName";

            Storage::makeDirectory('reports');

            $writer = new Xlsx($spreadsheet);
            $fullPath = storage_path("app/$filePath");
            $writer->save($fullPath);


            if (!file_exists($fullPath)) {
                throw new Exception("No se pudo guardar el archivo en: $filePath");
            }

            return [
                'status' => 200,
                'response' => [
                    'success' => true,
                    'message' => 'Reporte generado correctamente',
                    'file_path' => $filePath,
                ]
            ];
        } catch (Exception $e) {
            \Log::error('Error al generar el reporte: ' . $e->getMessage());

            return [
                'status' => 500,
                'response' => [
                    'success' => false,
                    'message' => 'Error al generar el reporte',
                    'error' => $e->getMessage(),
                ]
            ];
        }
    }
}
