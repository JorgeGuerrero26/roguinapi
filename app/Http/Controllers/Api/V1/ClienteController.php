<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class ClienteController extends Controller
{
    //Cambiado
    public function listarClientes()
    {
        try {
            //Listar todos los clientes que su estado sea 1
            $clientes = Cliente::where('estado', 1)->get();
            return response()->json(['data' => $clientes, 'status' => 'true'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Listar clientes con estado 0

    public function listarClientesConEstado0()
    {
        try {
            //Listar todos los clientes que su estado sea 0
            $clientes = Cliente::where('estado', 0)->get();
            return response()->json(['data' => $clientes, 'status' => 'true'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    //Cambiado
    public function buscarClientes(Request $request)
    {
        //validar si ha traido documento y si tiene al menos un caracter
        try {
            if ($request->has('documento') && $request->get('documento') > 0) {
                //buscar clientesque tengan un documento parecido
                $cliente = Cliente::where('documento', 'like', '%' . $request->documento . '%')->get();
                //validar si existen clientes

                return response()->json([
                    'data' =>  $cliente,
                    'status' => 'true'
                ]);
            } else {
                //validar si envio el nombre
                if ($request->has('nombre') && $request->get('nombre') > 0) {
                    $cliente = Cliente::where('nombre', 'like', '%' . $request->nombre . '%')->get();
                    //validar que haya retornado un cliente
                    return response()->json([
                        'data' =>
                        $cliente,

                        'status' => 'true'
                    ]);
                } else {
                    //retornar todos los clientes con sus respectivas entregas
                    $clientes = Cliente::where('estado', 1)->get();
                    //recorrer a los clientes para sacar sus entregas                   
                    return response()->json([
                        'data' => $clientes,
                        'status' => 'true'
                    ]);
                }
            }
        } catch (\Throwable $th) {
            //retornar mensaje del error
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }

    public function insertarClientes(Request $request)
    {
        try {
            //Validar que se hayan enviado todos los parametros
            $this->validate($request, [
                'nombre' => 'required',
                'documento' => 'required|integer',
                'entregas' => 'required',
                'forma_pago' => 'required',
            ]);

            //validar que el documento ingresado sea un numero de 8 o 11 digitos
            if ((strlen($request->documento) == 8 || strlen($request->documento) == 11)) {
                //validar que no exista un cliente con dicho documento
                $cliente = Cliente::where('documento', $request->documento)->first();
                if (!$cliente) {

                    //Desactivar autocommit
                    DB::beginTransaction();
                    //Empezar a captuarar los datos del cliente
                    $cliente = new Cliente();
                    $cliente->nombre = $request->nombre;
                    $cliente->documento = $request->documento;
                    $cliente->forma_pago = $request->forma_pago;


                    //Hacer commit
                    $cliente->save();

                    //Obtener el json de entregas del cliente
                    $entregas = $request->entregas;

                    //convertir entregas en un array
                    $entregas = json_decode($entregas);

                    //Recorrer el json de entregas y validar que todos los campos esten llenos
                    foreach ($entregas as $entrega) {
                        if ($entrega->zona_entrega == '' || $entrega->direccion_entrega == '') {
                            return response()->json([
                                'data' => [
                                    'Faltan datos en las entregas'
                                ],
                                'status' => 'false'
                            ]);
                        }
                    }


                    //recorrer el json para inssertar en la tabla entregas
                    foreach ($entregas as $entrega) {
                        $objentrega = new Entrega();
                        $objentrega->cliente_id = $cliente->id;
                        $objentrega->zona_entrega = $entrega->zona_entrega;
                        $objentrega->direccion_entrega = $entrega->direccion_entrega;
                        $objentrega->save();
                    }
                    //Activar autocommit
                    DB::commit();
                    return response()->json([
                        'data' => [
                            'Cliente insertado correctamente'
                        ],
                        'status' => 'true'
                    ]);
                } else {
                    return response()->json([
                        'data' => [
                            'Cliente ya existe'
                        ],
                        'status' => 'false'
                    ]);
                }
            } else {
                return response()->json([
                    'data' => [
                        'Documento invalido'
                    ],
                    'status' => 'false'
                ]);
            }
        } catch (\Throwable $th) {
            //Hacer rollback
            DB::rollback();
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }


    public function actualizarClientes(Request $request)
    {
        try {
            //Validar que se hayan enviado todos los parametros
            $this->validate($request, [
                'nombre' => 'required',
                'documento' => 'required|integer',                
                'saldo_botellon' => 'required|integer',
                'estado' => 'required',
                'id' => 'required',
                'forma_pago' => 'required',
            ]);

            //validar que el documento ingresado sea un numero de 8 o 11 digitos
            if ((strlen($request->documento) == 8 || strlen($request->documento) == 11)) {
                DB::beginTransaction();
                //Empezar a captuarar los datos del cliente
                $this->validate($request, [
                    'documento' => 'unique:clientes,documento,' . $request->id,
                ]);
                $cliente = Cliente::find($request->id);
                //validar que no exista un cliente con dicho documento menos el que ya tiene asignado
                //Desactivar autocommit
                $cliente->nombre = $request->nombre;
                $cliente->documento = $request->documento;
                $cliente->estado = $request->estado;
                $cliente->saldo_botellon = $request->saldo_botellon;
                $cliente->forma_pago = $request->forma_pago;
                $cliente->save();
/*                 //Obtener el json de entregas del cliente
                $entregas = $request->entregas;
                //convertir entregas en un array
                $entregas = json_decode($entregas);

                //Recorrer el json de entregas y validar que todos los campos esten llenos
                foreach ($entregas as $entrega) {
                    if ($entrega->zona_entrega == '' || $entrega->direccion_entrega == '') {
                        return response()->json([
                            'data' => [
                                'Faltan datos en las entregas'
                            ],
                            'status' => 'false'
                        ]);
                    }
                }
                                
                //Eliminar todas las entregas que tenga el cliente actualizado
                Entrega::where('cliente_id', $request->id)->delete();
                //recorrer el json para inssertar en la tabla entregas
                foreach ($entregas as $entrega) {
                    $objentrega = new Entrega();
                    $objentrega->cliente_id = $cliente->id;
                    $objentrega->zona_entrega = $entrega->zona_entrega;
                    $objentrega->direccion_entrega = $entrega->direccion_entrega;
                    $objentrega->save();
                } */

                DB::commit();
                return response()->json([
                    'data' => [
                        'Cliente actualizado correctamente'
                    ],
                    'status' => 'true'
                ]);
            } else {
                return response()->json([
                    'data' => [
                        'Documento invalido'
                    ],
                    'status' => 'false'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'data' => [
                    $th->getMessage()
                ],
                'status' => 'false'
            ]);
        }
    }


    public function eliminarClientes(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $cliente = Cliente::find($request->id);
            $cliente->estado = 0;
            $cliente->save();
            return response()->json([
                'data' => [
                    'Cliente dado de baja correctamente'
                ],
                'status' => 'true'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => [
                    $th->getMessage()
                ],
                'data' => [
                    'Error al eliminar cliente, por favor eliminar todas las ventas relacionadas con este cliente'
                ],
                'status' => 'false'
            ]);
        }
    }

    public function buscarClientePorId(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $cliente = Cliente::find($request->id);
            $cliente->entregas = Entrega::where('cliente_id', $cliente->id)->get();
            return response()->json([
                'data' =>
                $cliente,
                'status' => 'true'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'data' =>
                $th->getMessage(),
                'status' => 'false'
            ]);
        }
    }

    public function darDeAltaCliente(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $cliente = Cliente::find($request->id);
            $cliente->estado = 1;
            $cliente->save();
            return response()->json([
                'data' => [
                    'Cliente dado de alta correctamente'
                ],
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

    public function listarZonasDeUnClienteDadoSuId(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $entregas = Entrega::where('cliente_id', $request->id)->get();
            //Sacer el nombre del cliente y agregarlo al array de entregas
            $cliente = Cliente::find($request->id);
            $entregas->cliente = $cliente->nombre;
            return response()->json([
                'data' => $entregas,
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


