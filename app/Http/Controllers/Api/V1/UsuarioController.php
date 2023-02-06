<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Tipo_usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{

    public function listarUsuarios()
    {
        try {
            


            $usuarios = DB::select('select id,nombre,email,clave,tipo_usuario_id as tipo_usuario,tipo_usuario_id,estado,created_at,updated_at from usuarios where estado = 1');
            //Agregar la descripcion del tipo de usuario_id
            foreach ($usuarios as $usuario) {
                $usuario->tipo_usuario = Tipo_usuario::find($usuario->tipo_usuario)->descripcion;
            }                 
        
            
           

            return response()->json(['data' => $usuarios,'status' => 'true'],200);            
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

    //Listar Usuario con estado 0

 
    public function listarUsuariosEstado0()
    {
        try {
            


            $usuarios = DB::select('select id,nombre,email,clave,tipo_usuario_id as tipo_usuario,tipo_usuario_id,estado,created_at,updated_at from usuarios where estado = 0');
            //Agregar la descripcion del tipo de usuario_id
            foreach ($usuarios as $usuario) {
                $usuario->tipo_usuario = Tipo_usuario::find($usuario->tipo_usuario)->descripcion;
            }                 
        
            
           

            return response()->json(['data' => $usuarios,'status' => 'true'],200);            
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }





    public function insertarUsuarios(Request $request)
    {
        try {
            $request->validate([
                'tipo_usuario_id' => 'required',
                'nombre' => 'required|unique:usuarios',                
                'email' => 'required|email|unique:usuarios',
                'clave' => 'required',
            ]);
            $usuario = Usuario::create($request->all());
            return response()->json(['data' =>'Usuario registrado con exito','status' => 'true'],200);            
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

    public function buscarUsuarios(Request $request)
    {
       
        try {
             //Validar que pueda buscar por nombre o email
            $request->validate([
                'nombre' => 'nullable|string',
                'email' => 'nullable|string',
            ]);
            //Si envio por el nombre buscar los nombres parecidos, si envio por email buscar los emails parecidos y si no envio nada traer todos los usuarios
            if ($request->nombre) {
                //Buscar nombres parecidos
                $usuarios = DB::select("select id,nombre,email,clave,tipo_usuario_id as tipo_usuario,tipo_usuario_id,estado,created_at,updated_at from usuarios where estado = 1 and nombre like '%".$request->nombre."%'");           
                foreach ($usuarios as $usuario) {
                    $usuario->tipo_usuario = Tipo_usuario::find($usuario->tipo_usuario)->descripcion;
                }                 
            
                //Si no lo encuentra
                if (count($usuarios) == 0) {
                    return response()->json(['data' => 'No se encontraron usuarios','status' => 'false'],404);
                }    
            } elseif ($request->email) {
                //Buscar emails parecidos
                $usuarios = DB::select("select id,nombre,email,clave,tipo_usuario_id as tipo_usuario,tipo_usuario_id,estado,created_at,updated_at from usuarios where estado = 1 and email like '%".$request->email."%'");           
                foreach ($usuarios as $usuario) {
                    $usuario->tipo_usuario = Tipo_usuario::find($usuario->tipo_usuario)->descripcion;
                }                 
            

                //Si no lo encuentra
                if (count($usuarios) == 0) {
                    return response()->json(['data' => 'No se encontraron usuarios','status' => 'false'],404);
                }
            } else {
                $usuarios = DB::select('select id,nombre,email,clave,tipo_usuario_id as tipo_usuario,tipo_usuario_id,estado,created_at,updated_at from usuarios where estado = 1');
                foreach ($usuarios as $usuario) {
                    $usuario->tipo_usuario = Tipo_usuario::find($usuario->tipo_usuario)->descripcion;
                }        
            }
            return response()->json(['data' => $usuarios,'status' => 'true'],200);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

 
    public function actualizarUsuarios(Request $request)
    {
        try {
            $request->validate([              
                'id' =>'required',  
                'tipo_usuario_id' => 'required',
                'nombre' => 'required|unique:usuarios,nombre,' . $request->id,
                'email' => 'required|email|unique:usuarios,email,' . $request->id,
                'estado' => 'required|boolean',
                'clave' => 'required',
            ]);
            $usuario = Usuario::find($request->id);
            $usuario->tipo_usuario_id = $request->tipo_usuario_id;
            $usuario->nombre = $request->nombre;
            $usuario->email = $request->email;
            $usuario->clave = Hash::make($request->clave);
            $usuario->estado = $request->estado;
            $usuario->save();


            return response()->json(['data' =>'Usuario actualizado con exito','status' => 'true'],200);            
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);            
        } 
    }


    public function eliminarUsuarios(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            //Cambiar el usuario a estado 0
            $usuario = Usuario::find($request->id);
            $usuario->estado = 0;
            $usuario->save();            
            return response()->json(['data' =>'Usuario dado de baja con exito','status' => 'true'],200);            
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }      
    
    public function loginUsuario(Request $request){
        try {
            $request->validate([
                'email' => 'required|email',
                'clave' => 'required',
            ]);
            $usuario = Usuario::where('email', $request->email)->first();
            if ($usuario) {
                if ($usuario->clave == $request->clave) {
                    return response()->json(['data' => $usuario,'status' => 'true'],200);
                } else {
                    return response()->json(['data' => 'Clave incorrecta','status' => 'false'],401);
                }
            } else {
                return response()->json(['data' => 'Usuario no encontrado','status' => 'false'],404);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

    public function listarTiposUsuarios() {
        try {
            $tiposUsuarios = Tipo_usuario::all();
            return response()->json(['data' => $tiposUsuarios,'status' => 'true'],200);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

    public function buscarUsuarioPorId(Request $request){
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $usuario = Usuario::find($request->id);          
            return response()->json(['data' => $usuario,'status' => 'true'],200);         
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }

    public function darDeAltaUsuario(Request $request){
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $usuario = Usuario::find($request->id);
            $usuario->estado = 1;
            $usuario->save();
            return response()->json(['data' => 'Usuario dado de alta con exito','status' => 'true'],200);         
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage(),'status' => 'false'], 500);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th->getMessage(),'status' => 'false'], 500);
        }
    }
       
}
