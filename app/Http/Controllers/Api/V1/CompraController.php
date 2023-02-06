<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Detalle_compra;
use App\Models\Proveedor;
use App\Models\Material;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //Cambiado
    public function listarCompras()
    {
        try {
            $compras = DB::select('Select  top 100 c.*,p.nombre as proveedor, u.nombre as usuario from compras c inner join proveedores p on c.proveedor_id = p.id inner join usuarios u on c.usuario_id = u.id order by 1 desc');
            return response()->json(['data' => $compras, 'status' => 'true'], 200);
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
    public function insertarCompras(Request $request)
    {
        try {
            //Validar que envie al menos un caracter
            $request->validate([
                'fecha' => 'required',
                'observacion' => 'nullable',
                'proveedor_id' => 'required',
                'usuario_id' => 'required',
                'detalle_compra' => 'required',
            ]);
            //Desactivar autocommit
            DB::beginTransaction();
            $compra = new Compra();
            $compra->fecha = $request->fecha;
            $compra->observacion = $request->observacion;

            $compra->proveedor_id = $request->proveedor_id;
            $compra->usuario_id = $request->usuario_id;
            $compra->save();
            $detalle_compra = $request->detalle_compra;
            $detalle_compra = json_decode($detalle_compra);

            foreach ($detalle_compra as $detalle) {
                if ($detalle->material_id == '' || $detalle->cantidad_comprada == '' || $detalle->precio_unitario == '') {
                    return response()->json(['data' => 'Debe llenar todos los campos del detalle de la compra', 'status' => 'false'], 500);
                }
            }

            foreach ($detalle_compra as $detalle) {
                if (!is_int($detalle->cantidad_comprada)) {
                    return response()->json(['data' => 'La cantidad comprada debe ser un numero entero', 'status' => 'false'], 500);
                }
                if (!is_numeric($detalle->precio_unitario)) {
                    return response()->json(['data' => 'El precio unitario debe ser un numero', 'status' => 'false'], 500);
                }
            }


            //Recorrer cada detalle de la compra para insertarlo en la tabla detalle_compra
            foreach ($detalle_compra as $detalle) {
                $detalle_compra = new Detalle_compra();
                $detalle_compra->compra_id = $compra->id;
                $detalle_compra->material_id = $detalle->material_id;
                $detalle_compra->cantidad_comprada = $detalle->cantidad_comprada;
                $detalle_compra->precio_unitario = $detalle->precio_unitario;
                $detalle_compra->save();
            }

            DB::commit();
            return response()->json(['data' => 'Compra insertada correctamente', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Compra  $compra
     * @return \Illuminate\Http\Response
     */
    public function buscarCompras_Id(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $compra = Compra::find($request->id);
            $compra->detalle_compra = Detalle_compra::where('compra_id', $compra->id)->get();
            //Calcular el total de la compra
            $total = 0;
            foreach ($compra->detalle_compra as $detalle) {
                $total += $detalle->precio_unitario * $detalle->cantidad_comprada;
            }
            $compra->total_compra = $total;
            //Agregar el nombre del proveedor y el nombre del usuario
            $compra->proveedor = Proveedor::find($compra->proveedor_id)->nombre;
            $compra->usuario = Usuario::find($compra->usuario_id)->nombre;
            //Agregar a cada detalle de compra el nombre del material
            foreach ($compra->detalle_compra as $detalle) {
                $detalle->material = Material::find($detalle->material_id)->descripcion;
                $detalle->material_id = (int) $detalle->material_id;

                $detalle->cantidad_comprada = (int) $detalle->cantidad_comprada;
                $detalle->precio_unitario = (float) $detalle->precio_unitario;
                // Convertir compra_id a entero
                $detalle->compra_id = (int) $detalle->compra_id;


            }

            $compra->proveedor_id =(int) $compra->proveedor_id;
            $compra->usuario_id =(int) $compra->usuario_id;
            return response()->json(['data' => $compra, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Cambiado
    public function buscarComprasPorFechas(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;
            //Validar si no se han enviado las fechas
            if ($fecha_inicio == '' && $fecha_fin == '') {
                //LLamar a la funcion listarCompras
                return $this->listarCompras();
            } else {
                //Validar si no se envio la fecha de fin
                if ($fecha_fin == '') {
                    //Darle el valor a la fecha fin de la ultima compra que se hizo
                    $fecha_fin = Compra::orderBy('fecha', 'desc')->first()->fecha;
                    return $this->buscarFechas($fecha_inicio, $fecha_fin);
                } else {
                    //Validar si no se envio la fecha de inicio
                    if ($fecha_inicio == '') {
                        //Darle el valor a la fecha de inicio de la primera compra que se hizo
                        $fecha_inicio = Compra::orderBy('fecha', 'asc')->first()->fecha;
                        return $this->buscarFechas($fecha_inicio, $fecha_fin);
                    } else {
                        //Validar si la fecha de inicio es mayor a la fecha de fin
                        if ($fecha_inicio > $fecha_fin) {
                            return response()->json(['data' => 'La fecha de inicio no puede ser mayor a la fecha de fin', 'status' => 'false'], 500);
                        } else {
                            return $this->buscarFechas($fecha_inicio, $fecha_fin);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Modularizando busqueda por fechas
    public function buscarFechas($fecha_inicio, $fecha_fin)
    {
        $compras = DB::select('Select c.*,p.nombre as proveedor, u.nombre as usuario from compras c inner join proveedores p on c.proveedor_id = p.id inner join usuarios u on c.usuario_id = u.id where c.fecha between ? and ?', [$fecha_inicio, $fecha_fin]);
        return response()->json(['data' => $compras, 'status' => 'true'], 200);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Compra  $compra
     * @return \Illuminate\Http\Response
     */
    public function actualizarCompras(Request $request)
    {
        try {
            //Validar que envie al menos un caracter
            $request->validate([
                'id' => 'required',
                'fecha' => 'required',
                'observacion' => 'nullable',
                'proveedor_id' => 'required',
                'usuario_id' => 'required',
                'detalle_compra' => 'required',
            ]);
            //Desactivar autocommit
            DB::beginTransaction();

            $compra = Compra::find($request->id);
            $compra->fecha = $request->fecha;
            $compra->observacion = $request->observacion;
            $compra->proveedor_id = $request->proveedor_id;
            $compra->usuario_id = $request->usuario_id;
            $compra->save();
            $detalle_compra = $request->detalle_compra;
            $detalle_compra = json_decode($detalle_compra);
            //Recorrer el json de detalle_compra y validar que no existan valores vacios
            foreach ($detalle_compra as $detalle) {
                if ($detalle->material_id == '' || $detalle->cantidad_comprada == '' || $detalle->precio_unitario == '') {
                    return response()->json(['data' => 'Debe llenar todos los campos del detalle de la compra', 'status' => 'false'], 500);
                }
            }

            //Recorrer el json de detalle_compra y validar que cantidad_comprada sea un numero entero y precio unitario sea un numero
            foreach ($detalle_compra as $detalle) {
                if (!is_int($detalle->cantidad_comprada)) {
                    return response()->json(['data' => 'La cantidad comprada debe ser un numero entero', 'status' => 'false'], 500);
                }
                if (!is_numeric($detalle->precio_unitario)) {
                    return response()->json(['data' => 'El precio unitario debe ser un numero', 'status' => 'false'], 500);
                }
            }

            //Recorrer cada detalle de la compra para actualizarlo en la tabla detalle_compra
            Detalle_compra::where('compra_id', $compra->id)->delete();
            foreach ($detalle_compra as $detalle) {
                $detalle_compra = new Detalle_compra();
                $detalle_compra->compra_id = $compra->id;
                $detalle_compra->material_id = $detalle->material_id;
                $detalle_compra->cantidad_comprada = $detalle->cantidad_comprada;
                $detalle_compra->precio_unitario = $detalle->precio_unitario;
                $detalle_compra->save();
            }
            DB::commit();
            return response()->json(['data' => 'Compra actualizada correctamente', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Compra  $compra
     * @return \Illuminate\Http\Response
     */
    public function eliminarCompras(Request $request)
    {
        //Eliminar antes de la compra, todos los detalles de compra
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $compra = Compra::find($request->id);
            Detalle_compra::where('compra_id', $compra->id)->delete();
            $compra->delete();
            return response()->json(['data' => 'Compra eliminada correctamente', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }
    //Cambiado
    public function buscarComprasDeUnProveedor(Request $request)
    {
        try {
            $request->validate([
                'proveedor_id' => 'nullable|integer',
            ]);
            //Si no envia el id traer todas las compras
            if ($request->proveedor_id == null) {
                return $this->buscarCompras(null);
            } else {
                return $this->buscarCompras($request->proveedor_id);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Crear una funcion con parametro proveedor_id con valor 0 por defecto
    public function buscarCompras($proveedor_id = null)
    {
        if ($proveedor_id == null) {
                return $this->listarCompras();
        } else {
            $compras = DB::select('Select c.*,p.nombre as proveedor, u.nombre as usuario from compras c inner join proveedores p on c.proveedor_id = p.id inner join usuarios u on c.usuario_id = u.id where c.proveedor_id = ?', [$proveedor_id]);
            return response()->json(['data' => $compras, 'status' => 'true'], 200);
        }
    }
}
