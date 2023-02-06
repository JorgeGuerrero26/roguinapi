<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/





Route::middleware('auth:sanctum')->get('/user', 
function (Request $request) {
    //Retornar un json con data:true
    return response()->json(['data' => true], 200); 
}
);







//Usuarios
Route::post('/register', 'App\Http\Controllers\Api\V1\AuthController@register');
Route::post('/login', 'App\Http\Controllers\Api\V1\AuthController@login');




Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', 'App\Http\Controllers\Api\V1\AuthController@logout');

    //Emtregas

    Route::get('/listarEntregas', 'App\Http\Controllers\Api\V1\EntregaController@listarEntregas');
    Route::post('/insertarEntregas', 'App\Http\Controllers\Api\V1\EntregaController@insertarEntregas');
    Route::post('/buscarEntregaPorID', 'App\Http\Controllers\Api\V1\EntregaController@buscarEntregaPorID');
    Route::post('/actualizarEntregas', 'App\Http\Controllers\Api\V1\EntregaController@actualizarEntregas');
    Route::post('/eliminarEntregas', 'App\Http\Controllers\Api\V1\EntregaController@eliminarEntregas');
    Route::post('/buscarEntregaPorZonaODireccion', 'App\Http\Controllers\Api\V1\EntregaController@buscarEntregaPorZonaODireccion');
    Route::post('/darDeAltaEntregas', 'App\Http\Controllers\Api\V1\EntregaController@darDeAltaEntregas');
    Route::get('/listarEntregasConEstado0', 'App\Http\Controllers\Api\V1\EntregaController@listarEntregasConEstado0');


    Route::get('/listarClientes', 'App\Http\Controllers\Api\V1\ClienteController@listarClientes');
    //Clientes
    Route::post('/buscarClientes', 'App\Http\Controllers\Api\V1\ClienteController@buscarClientes');

    Route::post('/insertarClientes', 'App\Http\Controllers\Api\V1\ClienteController@insertarClientes');
    Route::post('/actualizarClientes', 'App\Http\Controllers\Api\V1\ClienteController@actualizarClientes');
    Route::post('/eliminarClientes', 'App\Http\Controllers\Api\V1\ClienteController@eliminarClientes');
    Route::post('buscarClientePorId', 'App\Http\Controllers\Api\V1\ClienteController@buscarClientePorId');
    Route::post('darDeAltaCliente', 'App\Http\Controllers\Api\V1\ClienteController@darDeAltaCliente');
    Route::get('listarClientesConEstado0', 'App\Http\Controllers\Api\V1\ClienteController@listarClientesConEstado0');
    //21/09/2022
    Route::post('listarZonasDeUnClienteDadoSuId', 'App\Http\Controllers\Api\V1\ClienteController@listarZonasDeUnClienteDadoSuId');

    //Proveedores
    Route::get('/listarProveedores', 'App\Http\Controllers\Api\V1\ProveedorController@listarProveedores');
    Route::post('/insertarProveedores', 'App\Http\Controllers\Api\V1\ProveedorController@insertarProveedores');
    Route::post('/buscarProveedores', 'App\Http\Controllers\Api\V1\ProveedorController@buscarProveedores');
    Route::post('/actualizarProveedores', 'App\Http\Controllers\Api\V1\ProveedorController@actualizarProveedores');
    Route::post('/eliminarProveedores', 'App\Http\Controllers\Api\V1\ProveedorController@eliminarProveedores');
    Route::post('buscarProveedorPorId', 'App\Http\Controllers\Api\V1\ProveedorController@buscarProveedorPorId');
    Route::post('darDeAltaProveedor', 'App\Http\Controllers\Api\V1\ProveedorController@darDeAltaProveedor');
    Route::get('listarProveedoresConEstado0', 'App\Http\Controllers\Api\V1\ProveedorController@listarProveedoresConEstado0');

    //Usuarios (Esta por ver si usaremos estas rutas o las que nos proporciona laravel)
    Route::post('/buscarUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@buscarUsuarios');
    Route::get('/listarUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@listarUsuarios');
    Route::post('/insertarUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@insertarUsuarios');
    Route::post('/actualizarUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@actualizarUsuarios');
    Route::post('/eliminarUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@eliminarUsuarios');
    Route::post('/loginUsuario', 'App\Http\Controllers\Api\V1\UsuarioController@loginUsuario');
    Route::post('buscarUsuarioPorId', 'App\Http\Controllers\Api\V1\UsuarioController@buscarUsuarioPorId');
    Route::post('darDeAltaUsuario', 'App\Http\Controllers\Api\V1\UsuarioController@darDeAltaUsuario');
    Route::get('listarUsuariosEstado0', 'App\Http\Controllers\Api\V1\UsuarioController@listarUsuariosEstado0');

    //Materiales
    Route::post('/buscarMateriales', 'App\Http\Controllers\Api\V1\MaterialController@buscarMateriales');
    Route::get('/listarMateriales', 'App\Http\Controllers\Api\V1\MaterialController@listarMateriales');
    Route::post('/insertarMateriales', 'App\Http\Controllers\Api\V1\MaterialController@insertarMateriales');
    Route::post('/actualizarMateriales', 'App\Http\Controllers\Api\V1\MaterialController@actualizarMateriales');
    Route::post('/eliminarMateriales', 'App\Http\Controllers\Api\V1\MaterialController@eliminarMateriales');
    Route::post('buscarMaterialPorId', 'App\Http\Controllers\Api\V1\MaterialController@buscarMaterialPorId');
    Route::post('darDeAltaMaterial', 'App\Http\Controllers\Api\V1\MaterialController@darDeAltaMaterial');
    Route::get('listarMaterialesConEstado0', 'App\Http\Controllers\Api\V1\MaterialController@listarMaterialesConEstado0');
    //Ventas
    Route::post('/buscarVentasPorFechas', 'App\Http\Controllers\Api\V1\VentaController@buscarVentasPorFechas');
    Route::get('/listarVentas', 'App\Http\Controllers\Api\V1\VentaController@listarVentas');
    Route::post('/insertarVentas', 'App\Http\Controllers\Api\V1\VentaController@insertarVentas');
    Route::post('/actualizarVentas', 'App\Http\Controllers\Api\V1\VentaController@actualizarVentas');
    Route::post('/eliminarVentas', 'App\Http\Controllers\Api\V1\VentaController@eliminarVentas');
    Route::post('/buscarVentas_Id', 'App\Http\Controllers\Api\V1\VentaController@buscarVentas_Id');
    Route::post('buscarVentasPorCliente', 'App\Http\Controllers\Api\V1\VentaController@buscarVentasDeUnCliente');
    //Compras
    Route::post('/buscarComprasPorFechas', 'App\Http\Controllers\Api\V1\CompraController@buscarComprasPorFechas');
    Route::get('/listarCompras', 'App\Http\Controllers\Api\V1\CompraController@listarCompras');
    Route::post('/insertarCompras', 'App\Http\Controllers\Api\V1\CompraController@insertarCompras');
    Route::post('/actualizarCompras', 'App\Http\Controllers\Api\V1\CompraController@actualizarCompras');
    Route::post('/eliminarCompras', 'App\Http\Controllers\Api\V1\CompraController@eliminarCompras');
    Route::post('/buscarCompras_Id', 'App\Http\Controllers\Api\V1\CompraController@buscarCompras_Id');
    Route::post('buscarComprasPorProveedor', 'App\Http\Controllers\Api\V1\CompraController@buscarComprasDeUnProveedor');

    //DetalleDeCompras
    Route::post('/buscarDetalleDeCompraDadoSuID', 'App\Http\Controllers\Api\V1\Detalle_compraController@buscarDetalleDeCompraDadoSuID');

    //DetalleDeVentas
    Route::post('/buscarDetalleDeVentaDadoSuID', 'App\Http\Controllers\Api\V1\Detalle_ventaController@buscarDetalleDeVentaDadoSuID');

    //Entregas
    Route::post('/buscarEntregasDeUnClienteDadoSuID', 'App\Http\Controllers\Api\V1\EntregaController@buscarEntregasDeUnClienteDadoSuID');

    //Nuevo
    Route::get('listarTiposUsuarios', 'App\Http\Controllers\Api\V1\UsuarioController@listarTiposUsuarios');
    Route::get('listarTiposMateriales', 'App\Http\Controllers\Api\V1\MaterialController@listarTiposMateriales');
});






//ArreglarVentas
Route::post('/arregalarVentas', 'App\Http\Controllers\Api\V1\VentaController@arregalarVentas');
Route::post('/agregarDetallesAVentas', 'App\Http\Controllers\Api\V1\VentaController@agregarDetallesAVentas');
Route::post('/solucionarNegativos', 'App\Http\Controllers\Api\V1\VentaController@solucionarNegativos');
Route::post('/agregarEntregasALosClientesQueNoTienen', 'App\Http\Controllers\Api\V1\VentaController@agregarEntregasALosClientesQueNoTienen');
Route::post('/arreglarEntregasDeClientes', 'App\Http\Controllers\Api\V1\VentaController@arreglarEntregasDeClientes');
Route::post('/validarQueLaIdDeEntregaYLaIdDeClienteCorrespondan', 'App\Http\Controllers\Api\V1\VentaController@validarQueLaIdDeEntregaYLaIdDeClienteCorrespondan');
Route::post('/calcularSaldoBotellon', 'App\Http\Controllers\Api\V1\VentaController@calcularUltimoSaldoBotellon');
Route::post('/arreglarNegativos', 'App\Http\Controllers\Api\V1\VentaController@arreglarNegativos');
Route::post('/insertarVentasHastaLaFechaDeHoy', 'App\Http\Controllers\Api\V1\VentaController@insertarVentasHastaLaFechaDeHoy');
Route::post('/eliminarVentasConFechasDuplicadas', 'App\Http\Controllers\Api\V1\VentaController@eliminarVentasConFechasDuplicadas');
Route::post('/dejarSoloUnDetalleDeVentaParaCadaVenta', 'App\Http\Controllers\Api\V1\VentaController@dejarSoloUnDetalleDeVentaParaCadaVenta');
Route::post('/insertarVentasPredictivas', 'App\Http\Controllers\Api\V1\VentaController@insertarVentasPredictivas');
