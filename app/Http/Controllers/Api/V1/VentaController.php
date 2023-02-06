<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Detalle_venta;
use App\Models\Entrega;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\Usuario;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //Cambiado
    public function listarVentas()
    {
        try {
            //Traer las ultimas 100 ventas con todos los datos de las ventas ademas hacer inner join con cliente para saber el nombre del cliente, hacer inner join con usuario para saber el nombre del usuario y hacer inner join con entrega para saber la zona_entrega
            $ventas = DB::select('SELECT TOP 100 V.*, C.nombre as cliente, U.nombre as usuario, E.zona_entrega FROM ventas V INNER JOIN clientes C ON V.cliente_id = C.id INNER JOIN usuarios U ON V.usuario_id = U.id INNER JOIN entregas E ON V.entrega_id = E.id ORDER BY V.id DESC');
            return response()->json(['data' => $ventas, 'status' => 'true'], 200);
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
    public function insertarVentas(Request $request)
    {
        try {
            //Validar que envie al menos un caracter
            $request->validate([
                'fecha' => 'required',
                'observacion' => 'nullable',
                'numero_guia' => 'required',
                'cliente_id' => 'required',
                'usuario_id' => 'required',
                'detalle_venta' => 'required',
                'entrega_id' => 'required'
            ]);
            //Desactivar autocommit
            DB::beginTransaction();
            $venta = new Venta();
            $venta->fecha = $request->fecha;
            $venta->observacion = $request->observacion;
            $venta->numero_guia = $request->numero_guia;
            $venta->cliente_id = $request->cliente_id;
            $venta->usuario_id = $request->usuario_id;
            $venta->entrega_id = $request->entrega_id;
            $venta->save();
            $detalle_venta = $request->detalle_venta;
            $detalle_venta = json_decode($detalle_venta);


            //Recorrer el json de detalle_Venta y validar que envie materiaul_id, que envie el precio_unitario y sea un numero, que envie la cantidad_entregada y sea un numero entero y que envie la cantidad recibida y sea un numero entero
            foreach ($detalle_venta as $detalle) {
                if (!is_numeric($detalle->material_id)) {
                    return response()->json(['data' => 'El material id debe ser un numero', 'status' => 'false'], 500);
                }
                if (!is_numeric($detalle->precio_unitario)) {
                    return response()->json(['data' => 'El precio unitario debe ser un numero', 'status' => 'false'], 500);
                }
                if (!is_int($detalle->cantidad_entregada)) {
                    //validar que la cantidad entregada sea un numero entero
                    return response()->json(['data' => 'La cantidad entregada debe ser un numero entero', 'status' => 'false'], 500);
                }
                if (!is_int($detalle->cantidad_recibida)) {
                    return response()->json(['data' => 'La cantidad recibida debe ser un numero entero', 'status' => 'false'], 500);
                }
            }

            //Insertar el detalle de la venta
            foreach ($detalle_venta as $detalle) {
                $objdetalle_venta = new Detalle_venta();
                $objdetalle_venta->venta_id = $venta->id;
                $objdetalle_venta->material_id = $detalle->material_id;
                $objdetalle_venta->precio_unitario = $detalle->precio_unitario;
                $objdetalle_venta->cantidad_entregada = $detalle->cantidad_entregada;
                $objdetalle_venta->cantidad_recibida = $detalle->cantidad_recibida;
                $objdetalle_venta->save();
            }
            //Hacer commit
            DB::commit();

            return response()->json(['data' => 'Venta insertada', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            //Hacer rollback
            DB::rollback();
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            //Hacer rollback
            DB::rollback();
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function buscarVentas_Id(Request $request) //Se llamara a esta funcion cuando le de a "ver mas"
    {
        try {
            $venta = Venta::find($request->id);
            $venta->detalle_venta = Detalle_venta::where('venta_id', $venta->id)->get();
            //Agregar el nombre del cliente y el nombre del usuario
            $venta->cliente = Cliente::find($venta->cliente_id)->nombre;
            $venta->usuario = Usuario::find($venta->usuario_id)->nombre;
            $venta->entrega = Entrega::find($venta->entrega_id)->zona_entrega;
            //Calcular el total de la venta
            $total = 0;
            foreach ($venta->detalle_venta as $detalle) {
                $total += $detalle->precio_unitario * $detalle->cantidad_entregada;
            }
            $venta->total_venta = $total;
            //Agregar el nombre del material
            foreach ($venta->detalle_venta as $detalle) {
                $detalle->material = Material::find($detalle->material_id)->descripcion;
                $detalle->material_id = (int) $detalle->material_id;

                //Convertir precio_unitario a float
                //Convertir cantidad_entregada a int
                //Convertir cantidad_recibida a int

                $detalle->precio_unitario = (float) $detalle->precio_unitario;
                $detalle->cantidad_entregada = (int) $detalle->cantidad_entregada;
                $detalle->cantidad_recibida = (int) $detalle->cantidad_recibida;
            }

            $venta->cliente_id = (int) $venta->cliente_id;
            $venta->usuario_id = (int) $venta->usuario_id;
            $venta->entrega_id = (int) $venta->entrega_id;

            return response()->json(['data' => $venta, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Cambiado
    public function buscarVentasPorFechas(Request $request) //Preguntar si se deberia buscar tambien por cliente
    {
        try {
            //Validar que envie un rango de fechas y que sean fechas
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;
            //Validar que haya enviado la fecha inicio y fecha fin y si no lo envió traer todas las fechas
            if ($fecha_inicio == '' && $fecha_fin == '') {
                return $this->listarVentas();
            } else {
                if ($fecha_inicio == '') {
                    //Validar que la fecha fin sea mayor a la fecha inicio
                    if ($fecha_fin < $fecha_inicio) {
                        return response()->json(['data' => 'La fecha final debe ser mayor a la fecha de inicio', 'status' => 'false'], 500);
                    }
                    //Crear una fecha de inicio que sea igual a la fecha de la primera venta
                    $fecha_inicio = Venta::orderBy('fecha', 'asc')->first()->fecha;
                    return $this->buscarFechas($fecha_inicio, $fecha_fin);
                } else {
                    if ($fecha_fin == '') {
                        //Crear una fecha de fin que sea igual a la fecha de la ultima venta
                        $fecha_fin = Venta::orderBy('fecha', 'desc')->first()->fecha;
                        return $this->buscarFechas($fecha_inicio, $fecha_fin);
                    } else {
                        //Validar que la fecha fin sea mayor a la fecha inicio
                        if ($fecha_fin < $fecha_inicio) {
                            return response()->json(['data' => 'La fecha final debe ser mayor a la fecha de inicio', 'status' => 'false'], 500);
                        }
                        return $this->buscarFechas($fecha_inicio, $fecha_fin);
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
        $ventas = DB::select('SELECT TOP 100 V.*, C.nombre as cliente, U.nombre as usuario, E.zona_entrega FROM ventas V INNER JOIN clientes C ON V.cliente_id = C.id INNER JOIN usuarios U ON V.usuario_id = U.id INNER JOIN entregas E ON V.entrega_id = E.id WHERE V.fecha BETWEEN ? AND ? ORDER BY V.id DESC', [$fecha_inicio, $fecha_fin]);
        return response()->json(['data' => $ventas, 'status' => 'true'], 200);
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function actualizarVentas(Request $request)
    {
        try {
            //Validar que envie un rango de fechas y que sean fechas
            $request->validate([
                'id' => 'required|integer',
                'numero_guia' => 'required|string',
                'fecha' => 'required|date',
                'cliente_id' => 'required|integer',
                'usuario_id' => 'required|integer',
                'observacion' => 'nullable|string',
                'detalle_venta' => 'required|string',
                'entrega_id' => 'required|integer',
            ]);
            $venta = Venta::find($request->id);
            $venta->numero_guia = $request->numero_guia;
            $venta->fecha = $request->fecha;
            $venta->cliente_id = $request->cliente_id;
            $venta->usuario_id = $request->usuario_id;
            $venta->observacion = $request->observacion;
            $venta->entrega_id = $request->entrega_id;
            $venta->save();
            $detalle_venta = $request->detalle_venta;
            $detalle_venta = json_decode($detalle_venta);
            //Recorrer el json de detalle_venta y validar que no hayan valores vacios
            foreach ($detalle_venta as $detalle) {
                if (!is_numeric($detalle->material_id)) {
                    return response()->json(['data' => 'El material id debe ser un numero', 'status' => 'false'], 500);
                }
                if (!is_numeric($detalle->precio_unitario)) {
                    return response()->json(['data' => 'El precio unitario debe ser un numero', 'status' => 'false'], 500);
                }
                if (!is_int($detalle->cantidad_entregada)) {
                    //validar que la cantidad entregada sea un numero entero
                    return response()->json(['data' => 'La cantidad entregada debe ser un numero entero', 'status' => 'false'], 500);
                }
                if (!is_int($detalle->cantidad_recibida)) {
                    return response()->json(['data' => 'La cantidad recibida debe ser un numero entero', 'status' => 'false'], 500);
                }
            }
            //Eliminar el detalle de la venta
            Detalle_venta::where('venta_id', $venta->id)->delete();
            //Insertar el detalle de la venta
            foreach ($detalle_venta as $detalle) {
                $objdetalle_venta = new Detalle_venta();
                $objdetalle_venta->venta_id = $venta->id;
                $objdetalle_venta->material_id = $detalle->material_id;
                $objdetalle_venta->precio_unitario = $detalle->precio_unitario;
                $objdetalle_venta->cantidad_entregada = $detalle->cantidad_entregada;
                $objdetalle_venta->cantidad_recibida = $detalle->cantidad_recibida;
                $objdetalle_venta->save();
            }
            //Hacer commit
            DB::commit();
            return response()->json(['data' => 'Venta actualizada', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function eliminarVentas(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);
            $venta = Venta::find($request->id);
            //Eliminar el detalle de venta
            Detalle_venta::where('venta_id', $venta->id)->delete();
            $venta->delete();
            return response()->json(['data' => 'Venta eliminada', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //Cambiado
    public function buscarVentasDeUnCliente(Request $request)
    {
        try {
            $request->validate([
                'cliente_id' => 'nullable|integer',
            ]);
            if ($request->has('cliente_id') && $request->get('cliente_id') > 0) {
                $ventas = DB::select('SELECT TOP 100 V.*, C.nombre as cliente, U.nombre as usuario, E.zona_entrega FROM ventas V INNER JOIN clientes C ON V.cliente_id = C.id INNER JOIN usuarios U ON V.usuario_id = U.id INNER JOIN entregas E ON V.entrega_id = E.id WHERE V.cliente_id = ?', [$request->cliente_id]);
                return response()->json(['data' => $ventas, 'status' => 'true'], 200);
            } else {
                return $this->listarVentas();
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }






    //NO CONSIDERAR ESTAS FUNCIONES


    public function arregalarVentas()
    {
        try {
            set_time_limit(0);
            //Hacer una consulta sql para obtener las fechas de las primeras ventas de cada cliente
            $ventas = DB::select('SELECT cliente_id, MIN(fecha) as fecha FROM ventas GROUP BY cliente_id');

            //Actualizar la cantidad_recibida de cada detalle de venta a 0 ubicando la fecha de la venta obtenida en la consulta anterior
            foreach ($ventas as $venta) {
                DB::update('UPDATE detalle_ventas SET cantidad_recibida = 0 WHERE venta_id IN (SELECT id FROM ventas WHERE cliente_id = ? AND fecha = ?)', [$venta->cliente_id, $venta->fecha]);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function agregarDetallesAVentas()
    {
        try {
            //Buscar las ventas que no tienen detalle de venta y almacenarlos en un json
            $ventas = DB::select('SELECT id FROM ventas WHERE id NOT IN (SELECT venta_id FROM detalle_ventas)');
            //Recorrer cada venta y agregarle el detalle de venta con una cantidad_entregada aleatoria entre 20 y 60, con una cantidad recibida aleatoria entre 20 y 60, con un precio_unitario aleatorio entre 7 y 10 y con un material_id aleatorio entre 1,2 y 3
            //Quitar el tiempo de ejecucion
            set_time_limit(0);
            //Realizar el seed en la BD en las ventas

            foreach ($ventas as $venta) {
                $detalle_venta = new Detalle_venta();
                $detalle_venta->venta_id = $venta->id;
                $detalle_venta->material_id = rand(1, 3);
                $detalle_venta->precio_unitario = rand(7, 10);
                $detalle_venta->cantidad_entregada = rand(1, 5);
                $detalle_venta->cantidad_recibida = rand(1, 5);
                $detalle_venta->save();
            }
            //Volver a limitar el tiempo de ejecucion a 60 segundos
            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function solucionarNegativos()
    {
        //Eliminar todas las variables del script
        unset($ventas);
        $ventas = DB::select('select v.fecha,dv.id as key_venta,cliente_id,cantidad_recibida,SUM(cantidad_entregada-cantidad_recibida) OVER (partition by cliente_id order by v.fecha,dv.id) as saldo_botellon
        from dbo.ventas V inner join dbo.detalle_ventas dv on V.id = dv.venta_id
        inner join dbo.clientes cl on cl.id = v.cliente_id
        inner join dbo.materiales ma on ma.id = dv.material_id
        group by
        dv.id,v.entrega_id,cliente_id,dv.material_id,cl.documento,cantidad_entregada,cantidad_recibida,v.fecha,precio_unitario*cantidad_entregada
        order by cliente_id,fecha');
        $this->arreglarNegativos($ventas);
    }


    public function arreglarNegativos()
    {
        try {

            $ventas = DB::select('select v.fecha,dv.id as key_venta,cliente_id,cantidad_recibida,cantidad_entregada,SUM(cantidad_entregada-cantidad_recibida) OVER (partition by cliente_id order by v.fecha,dv.id) as saldo_botellon
            from dbo.ventas V inner join dbo.detalle_ventas dv on V.id = dv.venta_id
            inner join dbo.clientes cl on cl.id = v.cliente_id
            inner join dbo.materiales ma on ma.id = dv.material_id
            group by
            dv.id,v.entrega_id,cliente_id,dv.material_id,cl.documento,cantidad_entregada,cantidad_recibida,v.fecha,precio_unitario*cantidad_entregada
            order by cliente_id,fecha');

            //Aumentar la memoria disponible
            ini_set('memory_limit', '30000M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer una consulta a la bd

            //Recorrar cada venta hasta encontrar una venta con saldo_botellon negativo
            foreach ($ventas as $venta) {
                if ($venta->saldo_botellon < 0) {
                    //Validar si la cantidad recibida es mayor a 5, si no es mayor entonces no se reduciran 5 la cantidad recibido sino se aumentara en 5 la cantidad entregada                
                        //Reducir 5 la cantidad recibida
                        DB::update('UPDATE detalle_ventas SET cantidad_recibida = cantidad_recibida - 1 WHERE id = ?', [$venta->key_venta]);
                    
                    unset($venta);
                    //Liberar ram
                    gc_collect_cycles();
                    //llamar a la funcion cubrid_free_result($req) para liberar la memoria
                    //Liberar memoria
                    //Volver a llamar a la funcion
                    $this->arreglarNegativos();
                } else {
                    //Validar que el saldo no sea mayor a 20
                    if ($venta->saldo_botellon > 20) {                                                    
                        DB::update('UPDATE detalle_ventas SET cantidad_recibida = cantidad_recibida + 5 WHERE id = ?', [$venta->key_venta]);                      
                        unset($venta);
                        //Liberar ram
                        gc_collect_cycles();
                        //llamar a la funcion cubrid_free_result($req) para liberar la memoria
                        //Liberar memoria
                        //Volver a llamar a la funcion
                        $this->arreglarNegativos();
                    }
                }
            }
            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function agregarEntregasALosClientesQueNoTienen()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer una consulta a la bd
            $clientes = DB::select('SELECT id FROM clientes WHERE id NOT IN (SELECT cliente_id FROM entregas)');
            //Recorrer cada cliente y agregarle una entrega con una fecha aleatoria entre 2019-01-01 y 2020-12-31
            foreach ($clientes as $cliente) {
                $entrega = new Entrega();
                $entrega->cliente_id = $cliente->id;
                $entrega->direccion_entrega = 'Calle 1 # 2 - 3';
                $entrega->zona_entrega = 'Zona 1';
                $entrega->created_at = null;
                $entrega->updated_at = null;
                $entrega->save();
            }
            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }
    public function arreglarEntregasDeClientes()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer un for y recorrer las entregas de cada cliente

            for ($i = 1; $i <= 100; $i++) {
                //Hacer una consulta a la bd
                $ventas = DB::select('select * from entregas where cliente_id = ?', [$i]);

                //Almacenar la primera id en una variable y las demas id menos la primera id en un array
                $id = $ventas[0]->id;
                $ids = array();
                for ($j = 1; $j < count($ventas); $j++) {
                    array_push($ids, $ventas[$j]->id);
                }

                //Validar si tiene 2 entregas o mas
                if (count($ids) > 0) {
                    foreach ($ids as $id2) {
                        DB::update('UPDATE ventas SET entrega_id = ? WHERE entrega_id = ?', [$id, $id2]);
                        //Eliminar las entregas con las demas id
                        DB::delete('DELETE FROM entregas WHERE id = ?', [$id2]);
                    }
                }
            }



            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function validarQueLaIdDeEntregaYLaIdDeClienteCorrespondan()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer una consulta a la bd
            $ventas = DB::select('select * from ventas');

            foreach ($ventas as $venta) {
                //Validar que la entrega_id corresponda al cliente_id y si no corresponde colocar la que corresponda
                $entrega = DB::select('select * from entregas where id = ?', [$venta->entrega_id]);
                if ($entrega[0]->cliente_id != $venta->cliente_id) {
                    $entrega2 = DB::select('select * from entregas where cliente_id = ?', [$venta->cliente_id]);
                    DB::update('UPDATE ventas SET entrega_id = ? WHERE id = ?', [$entrega2[0]->id, $venta->id]);
                }
            }
            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function calcularSaldoBotellonActual()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer un for y recorrer las ventas de cada cliente

            for ($i = 1; $i <= 100; $i++) {
                //Hacer una consulta a la bd
                $ventas = DB::select('select * from ventas where cliente_id = ?', [$i]);

                //Recorrer las ventas de cada cliente
                for ($j = 0; $j < count($ventas); $j++) {

                    //Hacer un for con los detalles de ventas de cada venta, sumar todos los botellones entregados y restar los botellones recibidos

                    $detalles = DB::select('select * from detalle_ventas where venta_id = ?', [$ventas[$j]->id]);

                    $botellonesEntregados = 0;

                    $botellonesRecibidos = 0;

                    for ($k = 0; $k < count($detalles); $k++) {
                        $botellonesEntregados += $detalles[$k]->botellones_entregados;
                        $botellonesRecibidos += $detalles[$k]->botellones_recibidos;
                    }

                    $saldoBotellonActual = $botellonesEntregados - $botellonesRecibidos;
                    //Actualizar el saldo de botellon actual de cada cliente
                    DB::update('UPDATE clientes SET saldo_botellon = ? WHERE id = ?', [$saldoBotellonActual, $i]);
                }
            }



            set_time_limit(60);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function calcularUltimoSaldoBotellon()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);
            //Hacer un for y recorrer las ventas de cada cliente
            $ventas = DB::select('select v.fecha,dv.id as key_venta,cliente_id,cantidad_recibida,SUM(cantidad_entregada-cantidad_recibida) OVER (partition by cliente_id order by v.fecha,dv.id) as saldo_botellon
            from dbo.ventas V inner join dbo.detalle_ventas dv on V.id = dv.venta_id
            inner join dbo.clientes cl on cl.id = v.cliente_id
            inner join dbo.materiales ma on ma.id = dv.material_id
            group by
            dv.id,v.entrega_id,cliente_id,dv.material_id,cl.documento,cantidad_entregada,cantidad_recibida,v.fecha,precio_unitario*cantidad_entregada
            order by cliente_id,fecha');

            //Calcular la maxima fecha de cada cliente
            $maxFecha = DB::select('select max(fecha) as fecha,cliente_id from ventas group by cliente_id');

            //Buscar en el array de ventas el saldo de botellon de la maxima fecha de cada cliente
            for ($i = 0; $i < count($maxFecha); $i++) {
                for ($j = 0; $j < count($ventas); $j++) {
                    if ($maxFecha[$i]->fecha == $ventas[$j]->fecha && $maxFecha[$i]->cliente_id == $ventas[$j]->cliente_id) {
                        //Actualizar el saldo de botellon actual de cada cliente
                        DB::update('UPDATE clientes SET saldo_botellon = ? WHERE id = ?', [$ventas[$j]->saldo_botellon, $maxFecha[$i]->cliente_id]);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function insertarVentasHastaLaFechaDeHoy()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);

            //Calcular la cantidad de clientes que hay en la bd
            $clientes = DB::select('select * from clientes');

            //Insertar ventas desde la ultima venta hasta el dia de hoy para cada cliente
            for ($i = 0; $i < count($clientes); $i++) {
                $ventas = DB::select('select * from ventas where cliente_id = ?', [$clientes[$i]->id]);
                $ultimaVenta = $ventas[count($ventas) - 1];
                $fecha = $ultimaVenta->fecha;
                $fecha = date('Y-m-d', strtotime($fecha . ' + 1 days'));
                $hoy = date('Y-m-d');
                while ($fecha <= $hoy) {
                    //Generar un numero random entero entre 1 y 10
                    $usuario_id = rand(1, 10);
                    //Observancion es "no hay observacion"
                    $observacion = "no hay observacion";
                    $numero_guia = "no hay numero de guia";


                    DB::insert('INSERT INTO ventas (fecha, observacion,numero_guia,cliente_id,usuario_id,entrega_id) VALUES (?,?,?,?,?,?)', [$fecha, $observacion, $numero_guia, $clientes[$i]->id, $usuario_id, $ultimaVenta->entrega_id]);
                    //Generar un numero random entre 20 a 30
                    $cantidad = rand(20, 30);
                    //Aumentar ese numero a la fecha
                    $fecha = date('Y-m-d', strtotime($fecha . ' + ' .   $cantidad  . ' days'));
                }
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function eliminarVentasConFechasDuplicadas()
    {
        try {
            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);

            //Calcular la cantidad de clientes que hay en la bd
            $clientes = DB::select('select * from clientes');

            //Eliminar ventas con fechas duplicadas para cada cliente
            for ($i = 0; $i < count($clientes); $i++) {
                $ventas = DB::select('select * from ventas where cliente_id = ?', [$clientes[$i]->id]);
                for ($j = 0; $j < count($ventas); $j++) {
                    for ($k = 0; $k < count($ventas); $k++) {
                        if ($j != $k && $ventas[$j]->fecha == $ventas[$k]->fecha) {
                            //Eliminar detalle de ventas de la venta duplicada
                            DB::delete('DELETE FROM detalle_ventas WHERE venta_id = ?', [$ventas[$k]->id]);

                            DB::delete('DELETE FROM ventas WHERE id = ?', [$ventas[$k]->id]);
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


    public function dejarSoloUnDetalleDeVentaParaCadaVenta()
    {
        try {

            //Aumentar la memoria disponible
            ini_set('memory_limit', '4096M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);

            //Calcular la cantidad de clientes que hay en la bd
            $clientes = DB::select('select * from clientes');

            //Eliminar ventas con fechas duplicadas para cada cliente
            for ($i = 0; $i < count($clientes); $i++) {
                $ventas = DB::select('select * from ventas where cliente_id = ?', [$clientes[$i]->id]);
                for ($j = 0; $j < count($ventas); $j++) {
                    $detalleVentas = DB::select('select * from detalle_ventas where venta_id = ?', [$ventas[$j]->id]);
                    for ($k = 0; $k < count($detalleVentas); $k++) {
                        if ($k != 0) {
                            DB::delete('DELETE FROM detalle_ventas WHERE id = ?', [$detalleVentas[$k]->id]);
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


    /* FECHA BIDONES_LLENOS	BIDONES_VACIOS	SALDO
				
20/02/2017		10	0	10
27/02/2017		10	10	10
09/03/2017		10	8	12
04/04/2017		10	10	12
09/05/2017		10	8	14
25/05/2017		10	12	12
17/06/2017		10	12	10
30/06/2017		10	4	16
12/07/2017		5	5	16
22/08/2017		10	15	11
04/09/2017		5	5	11
18/09/2017		5	5	11
05/10/2017		10	10	11
24/10/2017		10	10	11
12/12/2017		10	10	11
22/12/2017		10	10	11
28/12/2017		10	4	17
19/01/2018		10	10	17
24/01/2018		40	12	45
27/02/2018		40	40	45
05/04/2018		40	41	44
17/05/2018		40	39	45
11/08/2018		40	43	42
05/11/2018		40	31	51
28/12/2018		40	41	50
07/02/2019		40	42	48
15/03/2019		40	44	44
23/04/2019		40	35	49
13/06/2019		40	40	49
05/08/2019		40	35	54
02/10/2019		40	35	59
03/12/2019		40	39	60
16/01/2020		40	38	62
21/02/2020		40	43	59
19/03/2020		40	33	66
06/06/2020		40	42	64
08/09/2020		40	40	64
04/12/2020		40	39	65
27/01/2021		40	38	67
06/03/2021		40	40	67
08/04/2021		40	35	72
16/06/2021		40	39	73
06/09/2021		40	38	75
22/12/2021		40	43	72
23/03/2022		40	40	72

        */
    public function rehacerTodo()
    {
        try {
            //Eliminar detalle de ventas de cliente 24
            DB::delete('DELETE FROM detalle_ventas WHERE venta_id IN (SELECT id FROM ventas WHERE cliente_id = 24)');
            //Eliminar todas las ventas de cliente 24
            DB::delete('DELETE FROM ventas WHERE cliente_id = 24');

            //Agregar ventas basado en la matriz de arriba
            //Crear array con todas las fechas
            $fechas = array(
                "2017-02-20",
                "2017-02-27",
                "2017-03-09",
                "2017-04-04",
                "2017-05-09",
                "2017-05-25",
                "2017-06-17",
                "2017-06-30",
                "2017-07-12",
                "2017-08-22",
                "2017-09-04",
                "2017-09-18",
                "2017-10-05",
                "2017-10-24",
                "2017-12-12",
                "2017-12-22",
                "2017-12-28",
                "2018-01-19",
                "2018-01-24",
                "2018-02-27",
                "2018-04-05",
                "2018-05-17",
                "2018-08-11",
                "2018-11-05",
                "2018-12-28",
                "2019-02-07",
                "2019-03-15",
                "2019-04-23",
                "2019-06-13",
                "2019-08-05",
                "2019-10-02",
                "2019-12-03",
                "2020-01-16",
                "2020-02-21",
                "2020-03-19",
                "2020-06-06",
                "2020-09-08",
                "2020-12-04",
                "2021-01-27",
                "2021-03-06",
                "2021-04-08",
                "2021-06-16",
                "2021-09-06",
                "2021-12-22",
                "2022-03-23"
            );

            //Crear array con todas las cantidades de bidones llenos
            $bidones_llenos = array(
                10,
                10,
                10,
                10,
                10,
                10,
                10,
                10,
                5,
                10,
                5,
                5,
                10,
                10,
                10,
                10,
                10,
                10,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40,
                40
            );

            //Crear array con todas las cantidades de bidones vacios
            $bidones_vacios = array(
                0,
                10,
                8,
                10,
                8,
                12,
                12,
                4,
                5,
                15,
                5,
                5,
                10,
                10,
                10,
                10,
                4,
                10,
                12,
                40,
                41,
                39,
                43,
                31,
                41,
                42,
                44,
                35,
                40,
                35,
                35,
                39,
                38,
                43,
                33,
                42,
                40,
                39,
                38,
                43,
                40
            );

            //Registrar ventas en las fechas del array para el cliente 24,usuario_id 1 y entrega_id 205
            for ($i = 0; $i < count($fechas); $i++) {
                $venta = new Venta();
                $venta->cliente_id = 24;
                $venta->usuario_id = 1;
                $venta->entrega_id = 205;
                $venta->fecha = $fechas[$i];
                $venta->save();
                //Registrar detalle de venta para bidones_entregados y bidones_recibidos
                $detalle_venta = new Detalle_venta();
                $detalle_venta->venta_id = $venta->id;
                $detalle_venta->material_id = 1;
                $detalle_venta->precio_unitario = 10;
                $detalle_venta->cantidad_entregada = $bidones_llenos[$i];
                $detalle_venta->cantidad_recibida = $bidones_vacios[$i];
                $detalle_venta->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function insertarVentasPredictivas()
    {
        try {

            //Aumentar la memoria disponible
            ini_set('memory_limit', '30000M');
            //Desactivar el tiempo de ejecucion
            set_time_limit(0);   

            for ($i = 46; $i <= 111; $i++) {

                //Sacar la entrega_id de ese cliente buscandolo en la tabla entrega
                $entrega_id = DB::table('entregas')->where('cliente_id', $i)->value('id');

                //Iniciar fecha en 2015-01-01
                $fecha = "2015-01-01";

                //Numero random entre 1 y 3
                $validacion_intervalo_fechas = rand(1, 3);

                //Numero random de 1 a 3
                $validacion_cantidad_entregada = rand(1, 3);



                //Si el numero es 1 las ventas seran cada 1 a 4 dias, si el numero es 2 las ventas seran cada 3 a 6 dias, si el numero es 3 las ventas seran cada 7 a 10 dias
                if ($validacion_intervalo_fechas == 1) {
                    
                    //Insertar ventas dependiendo del intervalo de fechas hasta la fecha actual

                    while ($fecha <= date('Y-m-d')) {
                        
                        $intervalo_fechas = rand(2, 7);
                        
                        $fecha = date('Y-m-d', strtotime($fecha . ' + ' . $intervalo_fechas . ' days'));
                       
                        $venta = new Venta();
                        $venta->cliente_id = $i;
                        $venta->usuario_id = 1;
                        $venta->entrega_id = $entrega_id;
                        $venta->fecha = $fecha;
                        $venta->save();

                        //Si el numero es 1 la cantidad entregada seran entre 1 y 4, si el numero es 2 la cantidad entregada seran entre 5 y 8, si el numero es 3 la cantidad entregada seran entre 9 y 12}
                        if ($validacion_cantidad_entregada == 1) {

                            $cantidad_entregada = rand(1, 4);
                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 2) {
                            $cantidad_entregada = rand(5, 8);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 3) {
                            $cantidad_entregada = rand(9, 12);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        }



                        //Insertar detalle de venta para bidones_entregados y bidones_recibidos
                        $detalle_venta = new Detalle_venta();
                        $detalle_venta->venta_id = $venta->id;
                        $detalle_venta->material_id = rand(1, 3);
                        $detalle_venta->precio_unitario = rand(7, 10);
                        $detalle_venta->cantidad_entregada = $cantidad_entregada;
                        $detalle_venta->cantidad_recibida = rand(1, 12);
                        $detalle_venta->save();

                        
                    }
                } elseif ($validacion_intervalo_fechas == 2) {
                   
                    while ($fecha <= date('Y-m-d')) {

                        $intervalo_fechas = rand(6, 10);
                        $fecha = date('Y-m-d', strtotime($fecha . ' + ' . $intervalo_fechas . ' days'));


                       
                        $venta = new Venta();
                        $venta->cliente_id = $i;
                        $venta->usuario_id = 1;
                        $venta->entrega_id = $entrega_id;
                        $venta->fecha = $fecha;
                        $venta->save();

                        //Si el numero es 1 la cantidad entregada seran entre 1 y 4, si el numero es 2 la cantidad entregada seran entre 5 y 8, si el numero es 3 la cantidad entregada seran entre 9 y 12}
                        if ($validacion_cantidad_entregada == 1) {

                            $cantidad_entregada = rand(1, 4);
                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 2) {
                            $cantidad_entregada = rand(5, 8);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 3) {
                            $cantidad_entregada = rand(9, 12);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        }



                        //Insertar detalle de venta para bidones_entregados y bidones_recibidos
                        $detalle_venta = new Detalle_venta();
                        $detalle_venta->venta_id = $venta->id;
                        $detalle_venta->material_id = rand(1, 3);
                        $detalle_venta->precio_unitario = rand(7, 10);
                        $detalle_venta->cantidad_entregada = $cantidad_entregada;
                        $detalle_venta->cantidad_recibida = rand(1, 12);
                        $detalle_venta->save();
                    }
                } else {

                    while ($fecha <= date('Y-m-d')) {

                        $intervalo_fechas = rand(8, 15);

                        $fecha = date('Y-m-d', strtotime($fecha . ' + ' . $intervalo_fechas . ' days'));
                        
                        $venta = new Venta();
                        $venta->cliente_id = $i;
                        $venta->usuario_id = 1;
                        $venta->entrega_id = $entrega_id;
                        $venta->fecha = $fecha;
                        $venta->save();

                        //Si el numero es 1 la cantidad entregada seran entre 1 y 4, si el numero es 2 la cantidad entregada seran entre 5 y 8, si el numero es 3 la cantidad entregada seran entre 9 y 12}
                        if ($validacion_cantidad_entregada == 1) {

                            $cantidad_entregada = rand(1, 4);
                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 2) {
                            $cantidad_entregada = rand(5, 8);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        } elseif ($validacion_cantidad_entregada == 3) {
                            $cantidad_entregada = rand(9, 12);

                            //Si son los meses de diciembre, enero y febrero la cantidad entregada aumentara en 2
                            if (date('m', strtotime($fecha)) == 12 || date('m', strtotime($fecha)) == 1 || date('m', strtotime($fecha)) == 2) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            }

                            //Si el año es 2016 la cantidad entregada aumenta en 1, si es 2017 aumenta en 2, si es 2018 aumenta en 2, si es 2019 aumenta en 3, si es 2020 aumenta en 3, si es 2021 aumenta en 3, si es 2022 aumenta en 4
                            if (date('Y', strtotime($fecha)) == 2016) {
                                $cantidad_entregada = $cantidad_entregada + 1;
                            } elseif (date('Y', strtotime($fecha)) == 2017) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2018) {
                                $cantidad_entregada = $cantidad_entregada + 2;
                            } elseif (date('Y', strtotime($fecha)) == 2019) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2020) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2021) {
                                $cantidad_entregada = $cantidad_entregada + 3;
                            } elseif (date('Y', strtotime($fecha)) == 2022) {
                                $cantidad_entregada = $cantidad_entregada + 4;
                            }
                        }



                        //Insertar detalle de venta para bidones_entregados y bidones_recibidos
                        $detalle_venta = new Detalle_venta();
                        $detalle_venta->venta_id = $venta->id;
                        $detalle_venta->material_id = rand(1, 3);
                        $detalle_venta->precio_unitario = rand(7, 10);
                        $detalle_venta->cantidad_entregada = $cantidad_entregada;
                        $detalle_venta->cantidad_recibida = rand(1, 12);
                        $detalle_venta->save();                      
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function eliminarVentasQueSuperanLaFechaActual(){
        $ventas = Venta::all();

        foreach ($ventas as $venta) {
            if (date('Y-m-d', strtotime($venta->fecha)) > date('Y-m-d')) {
                //Eliminar el detalle de venta
                $detalle_venta = Detalle_venta::where('venta_id', $venta->id)->first();
                $detalle_venta->delete();
                $venta->delete();
            }
        }
    }
}
