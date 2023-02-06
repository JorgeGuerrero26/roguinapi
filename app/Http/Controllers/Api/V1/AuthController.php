<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;


use \stdClass;



class AuthController extends Controller
{

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'tipo_usuario_id' => 'required',
            'nombre' => 'required',
            'estado' => 'required',
            'email' => 'required|email|unique:usuarios',
            'clave' => 'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $usuario = Usuario::create([
            'tipo_usuario_id' => $request->tipo_usuario_id,
            'nombre' => $request->nombre,
            'estado' => $request->estado,
            'email' => $request->email,
            'clave' => Hash::make($request->clave)
        ]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'data'=>$usuario,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);

    }

    public function login(Request $request){
        //Obtener la clave y cambiarla por password
        $request->request->add(['password' => $request->clave]);
        $request->request->remove('clave');

        //Verificar credenciales
        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $usuario = Usuario::where('email', $request->email)->firstOrFail();

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'data'=>$usuario,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);

            
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'data' => 'Sesión cerrada'
        ], 200);       
    }

    //Crear una funcion en se envie el token y retorne true o false

    public function checkToken(){
        return true;
    }



}


