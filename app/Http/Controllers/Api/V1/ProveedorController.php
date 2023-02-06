<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{

    public function listarProveedores()
    {
        try {
            //Listar proveedores que esten activos
            $proveedores = Proveedor::where('estado', 1)->get();

            return response()->json(['data' => $proveedores, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Listar Proveedores con estado 0

    public function listarProveedoresConEstado0()
    {
        try {
            //Listar proveedores que esten activos
            $proveedores = Proveedor::where('estado', 0)->get();

            return response()->json(['data' => $proveedores, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function insertarProveedores(Request $request)
    {
        try {
            //validar que el ruc sea unicamente de 11 caracteres numericos
            $request->validate([
                'ruc' => 'required|unique:proveedores|digits:11|integer',
                //Validar que envie al menos un caracter
                'nombre' => 'required',
            ]);

            $proveedor = new Proveedor();
            $proveedor->ruc = $request->ruc;
            $proveedor->nombre = $request->nombre;
            $proveedor->save();

            return response()->json(['data' => 'Proveedor insertado', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function buscarProveedores(Request $request)
    {
        try {
            //Validar que pueda buscar por el nombre o por el ruc
            $request->validate([
                'ruc' => 'nullable|integer',
                'nombre' => 'nullable',
            ]);
            //Si envio el ruc buscar por los que se parezcan a ese ruc, si envio el nombre buscar  por los que se parezcan a ese nombre y si no envio nada buscar todos
            if ($request->ruc) {
                //Buscar ruc parecidos
                $proveedores = Proveedor::where('ruc', 'like', '%' . $request->ruc . '%')->get();
                //Si no lo encuentra
                if (count($proveedores) == 0) {
                    return response()->json(['data' => 'No se encontraron proveedores', 'status' => 'false'], 404);
                }
            } elseif ($request->nombre) {
                //Buscar nombres parecidos
                $proveedores = Proveedor::where('nombre', 'like', '%' . $request->nombre . '%')->get();
                //Si no lo encuentra
                if (count($proveedores) == 0) {
                    return response()->json(['data' => 'No se encontraron proveedores', 'status' => 'false'], 404);
                }
            } else {
                $proveedores = Proveedor::where('estado', 1)->get();
            }
            return response()->json(['data' => $proveedores, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function actualizarProveedores(Request $request)
    {
        try {
            //Validar que el ruc sea numerico y que tenga 11 caracteres, ademas validar que el ruc sea unico sin tomar en cuenta el actuall
            $request->validate([
                'id' => 'required|integer',
                'ruc' => 'required|unique:proveedores,ruc,' . $request->id . '|digits:11|integer',
                'nombre' => 'required',
                'estado' => 'required',
            ]);
            $proveedor = Proveedor::find($request->id);
            $proveedor->ruc = $request->ruc;
            $proveedor->nombre = $request->nombre;
            $proveedor->estado = $request->estado;
            $proveedor->save();

            return response()->json(['data' => 'Proveedor actualizado', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function eliminarProveedores(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $proveedor = Proveedor::find($request->id);
            $proveedor->estado = 0;
            $proveedor->save();

            return response()->json(['data' => 'Proveedor dado de baja con exito', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function buscarProveedorPorId(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $proveedor = Proveedor::find($request->id);
            return response()->json(['data' => $proveedor, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }


    public function darDeAltaProveedor(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $proveedor = Proveedor::find($request->id);
            $proveedor->estado = 1;
            $proveedor->save();

            return response()->json(['data' => 'Proveedor dado de alta con exito', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }
}
