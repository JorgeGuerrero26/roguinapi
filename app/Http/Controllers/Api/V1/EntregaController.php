<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as BD;

class EntregaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function buscarEntregasDeUnClienteDadoSuID(Request $request)
    {
        
        $entregas = Entrega::where('cliente_id', $request->id)->get();
        //Hacer un for y buscar el nombre del cliente
        foreach ($entregas as $entrega) {
            $cliente = Cliente::find($entrega->cliente_id);
            $entrega->cliente = $cliente->nombre;
        }
        


        return response()->json(['id'=> $request->id,'data' => $entregas, 'status' => 'true'], 200);
    }

    //Listar Entregas

    public function listarEntregas()
    {
        $entregas = BD::SELECT('SELECT e.*,c.nombre as cliente FROM entregas e inner join clientes c on c.id = e.cliente_id WHERE e.estado = 1 order by 1 desc');     
        return response()->json(['data' => $entregas, 'status' => 'true'], 200);
    }

    //Listar entregas con estado 0

    public function listarEntregasConEstado0()
    {
        $entregas = BD::SELECT('SELECT e.*,c.nombre as cliente FROM entregas e inner join clientes c on c.id = e.cliente_id WHERE e.estado = 0 order by 1 desc');     
        return response()->json(['data' => $entregas, 'status' => 'true'], 200);
    }

    //Insertar una nueva entrega

    public function insertarEntregas(Request $request)
    {
        try {

            $this->validate($request, [
                'zona_entrega' => 'required',
                'direccion_entrega' => 'required',
                'cliente_id' => 'required',   
            ]);

            $entrega = new Entrega();
            $entrega->zona_entrega = $request->zona_entrega;
            $entrega->direccion_entrega = $request->direccion_entrega;
            $entrega->cliente_id = $request->cliente_id;
            $entrega->save();

            return response()->json([
                'data' => 'Entrega insertada correctamente',
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Actualizar una entrega

    public function actualizarEntregas(Request $request)
    {
        try {

            $this->validate($request, [
                'id' => 'required',
                'zona_entrega' => 'required',
                'direccion_entrega' => 'required',
                'cliente_id' => 'required',   
            ]);

            //Actualizar las ventas y poner el cliente_id donde la zona de entrega sea esta

            BD::update('UPDATE ventas SET cliente_id = ? WHERE entrega_id = ?', [$request->cliente_id, $request->id]);

            $entrega = Entrega::find($request->id);
            $entrega->zona_entrega = $request->zona_entrega;
            $entrega->direccion_entrega = $request->direccion_entrega;
            $entrega->cliente_id = $request->cliente_id;
            $entrega->save();

            return response()->json([
                'data' => 'Entrega actualizada correctamente',
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }


    //Eliminar una entrega (En readlidad lo que hace es cambiar el estado a 0)

    public function eliminarEntregas(Request $request)
    {
        try {

            $this->validate($request, [
                'id' => 'required',
            ]);

            $entrega = Entrega::find($request->id);
            $entrega->estado = 0;
            $entrega->save();

            return response()->json([
                'data' => 'Entrega eliminada correctamente',
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Dar de alta a entregas

    public function darDeAltaEntregas(Request $request)
    {
        try {

            $this->validate($request, [
                'id' => 'required',
            ]);

            $entrega = Entrega::find($request->id);
            $entrega->estado = 1;
            $entrega->save();

            return response()->json([
                'data' => 'Entrega dada de alta correctamente',
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Buscar una entrega por su ID

    public function buscarEntregaPorID(Request $request)
    {
        try {

            $this->validate($request, [
                'id' => 'required|numeric',
            ]);

            $entrega = Entrega::find($request->id);

            //Sacar el nombre del cliente

            $cliente = Cliente::find($entrega->cliente_id);

            $entrega->cliente = $cliente->nombre;

            //Cambiar el cliente_id a entero

            $entrega->cliente_id = (int)$entrega->cliente_id;

            //El estado tambien

            $entrega->estado = (int)$entrega->estado;

            return response()->json([
                'data' => $entrega,
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Buscar una entrega por zona de entrega o por direccion de entrega

    public function buscarEntregaPorZonaODireccion(Request $request)
    {
        try {

            $this->validate($request, [
                'zona_entrega' => 'nullable',
                'direccion_entrega' => 'nullable',
            ]);

            //Validar que si envia zona_entrega lo busca por zona de entrega con coincidencia, si envia direccion_entrega lo busca por direccion de entrega con coincidencia, si no envia nada trae todas las entregas

            if($request->zona_entrega != null){
                $entrega = Entrega::where('zona_entrega', 'like', '%'.$request->zona_entrega.'%')->get();
            }else if($request->direccion_entrega != null){
                $entrega = Entrega::where('direccion_entrega', 'like', '%'.$request->direccion_entrega.'%')->get();
            }else{
                $entrega = Entrega::all();
            }

            //Sacar el nombre del cliente

            foreach ($entrega as $key => $value) {
                $cliente = Cliente::find($value->cliente_id);
                $value->cliente = $cliente->nombre;
            }
                       
            return response()->json([
                'data' => $entrega,
                'status' => 'true'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }



 







}
