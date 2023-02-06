<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Tipo_material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listarMateriales()
    {
        try {
            //Listar materiales que esten activos
            $materiales = Material::where('estado',1)->get();
            return response()->json(['data' => $materiales, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    //ListarMaterialesConEstado0

    public function listarMaterialesConEstado0()
    {
        try {
            //Listar materiales que esten activos
            $materiales = Material::where('estado',0)->get();
            return response()->json(['data' => $materiales, 'status' => 'true'], 200);
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
    public function insertarMateriales(Request $request)
    {
        try {
            //validar que el ruc sea unicamente de 11 caracteres numericos
            $request->validate([                                           
                'descripcion' => 'required|unique:materiales',
                'precio' => 'required|numeric',               
            ]);      
            $material = new Material();                   
            $material->descripcion = $request->descripcion;
            $material->precio = $request->precio;
            $material->save();

 
            
 
            return response()->json(['data' => 'Material insertado', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function buscarMateriales(Request $request)
    {
        try {
            //validar que se pueda buscar por el nombre
            $request->validate([
                'descripcion' => 'nullable',
            ]);
            //Si trajo el nombre buscar nombres parecidos, si no traer a todos los materiales
            if ($request->descripcion) {
                //Buscar nombres parecidos
                $materiales = Material::where('descripcion', 'like', '%' . $request->descripcion . '%')->get();
                //si no lo encuentra
                if (count($materiales) == 0) {
                    return response()->json(['data' => 'No se encontraron materiales', 'status' => 'false'], 404);
                }
            } else {
                $materiales = Material::where('estado',1)->get();
            }
            return response()->json(['data' => $materiales, 'status' => 'true'], 200);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function actualizarMateriales(Request $request)
    {
        try {
            //que la descripcion sea unico ignorando el actual
            $request->validate([
                'id' => 'required',                    
                'descripcion' => 'required|unique:materiales,descripcion,' . $request->id,
                'stock_actual' => 'required|integer',   
                'estado' => 'required|boolean',
                'precio' => 'required|numeric',               
            ]);      
            $material = Material::find($request->id);                 
            $material->descripcion = $request->descripcion;
            $material->stock_actual = $request->stock_actual;
            $material->estado = $request->estado;
            $material->precio = $request->precio;
            $material->save();
            return response()->json(['data' => 'Material actualizado', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
            
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function eliminarMateriales(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $material = Material::find($request->id);
            $material->estado = 0;
            $material->save();
            
            
            
            return response()->json(['data' => 'Material dado de baja con exito', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

/*     public function listarTiposMateriales(){
        try {
            $tiposMateriales = Tipo_material::all();
            return response()->json(['data' => $tiposMateriales, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    } */

    public function buscarMaterialPorId(Request $request){
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $material = Material::find($request->id);
            return response()->json(['data' => $material, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

    public function darDeAltaMaterial(Request $request){
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $material = Material::find($request->id);
            $material->estado = 1;
            $material->save();
            return response()->json(['data' => 'Material dado de alta con exito', 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(), 'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(), 'status' => 'false'], 500);
        }
    }

}
