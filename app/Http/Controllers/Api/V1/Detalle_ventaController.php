<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Detalle_venta;
use App\Models\Material;
use Illuminate\Http\Request;

class Detalle_ventaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function buscarDetalleDeVentaDadoSuID(Request $request){
        $detalle_venta = Detalle_venta::where('venta_id', $request->id)->get();
        //Recorrer el detalle de venta y agregar el nombre del material
        foreach ($detalle_venta as $detalle) {
            $material = Material::where('id', $detalle->material_id)->first();
            $detalle->nombre_material = $material->descripcion;
            //Calcular el subtotal multiplicanddo precio_unitario por cantidad_comprada
            $detalle->subtotal = $detalle->precio_unitario * $detalle->cantidad_entregada;
        }

        //Calcular el total de todo el detalle
        $total = 0;
        foreach ($detalle_venta as $detalle) {
            $total = $total + $detalle->subtotal;
        }

        //Retornar el detalle de venta con el total
        return response()->json([
            'id' => $request->id,
            'data' => $detalle_venta,
            'total_venta' => $total,
            'status' => 'true'
        ]);

    }

}
