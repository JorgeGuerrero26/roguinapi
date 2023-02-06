<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Detalle_compra;
use App\Models\Material;

use Illuminate\Http\Request;

class Detalle_compraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function buscarDetalleDeCompraDadoSuID(Request $request)
    {

        //Buscar el detalle de compra de la compra con el id dado
        $detalle_compra = Detalle_compra::where('compra_id', $request->id)->get();
           //Recorrer el detalle de comprar y agregar el nombre del material
           foreach ($detalle_compra as $detalle) {
            $material = Material::where('id', $detalle->material_id)->first();
            $detalle->nombre_material = $material->descripcion;
            //Calcular el subtotal multiplicanddo precio_unitario por cantidad_comprada
            $detalle->subtotal = $detalle->precio_unitario * $detalle->cantidad_comprada;
        }

        //Calcular el total de todo el detalle
        $total = 0;
        foreach ($detalle_compra as $detalle) {
            $total = $total + $detalle->subtotal;
        }

        //Retornar el detalle de compra con el total
        return response()->json([
            'id' => $request->id,
            'data' => $detalle_compra,
            'total_compra' => $total,
            'status' => 'true'
        ]);     
        

    }


}
