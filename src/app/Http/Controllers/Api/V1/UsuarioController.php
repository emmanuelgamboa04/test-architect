<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator, File, DB, Exception, Auth;
use App\Models\User;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['usuario']]);
    }

     /**
    * @OA\Post(
    *     path="/api/v1/usuario",
    *     summary="Guardar usuario",    *      
    *      @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="name del usuario",
     *         required=true,
     *      ),
     * *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="email del usuario",
     *         required=true,
     *      ),
      * *     @OA\Parameter(
     *         name="clave",
     *         in="query",
     *         description="clave del usuario",
     *         required=true,
     *      ),
    *     @OA\Response(
    *         response=200,
    *         description="Guarda el usuario."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function usuario(Request $request)
    {
        if($request->name && $request->email && $request->clave){
            DB::beginTransaction();
            try{
                $usuarioModel = DB::table('users')
                                ->where('email', $request->email) 
                                ->select('*')
                                ->first();
                if($usuarioModel){
                    return response()->json(["estado" => "error", "mensaje" => "Se encontro un usuario con el mismo email.", "data" => null]);
                }

                $usuario = new User();
                $usuario->name = $request->name;
                $usuario->email = $request->email;
                $usuario->password = Hash::make($request->clave);
                $usuario->save();
                DB::commit();
                return response()->json(["estado" => "success", "mensaje" => "Se registro con exito el usuario.", "data" => null]);
            }catch(\Exception $exception){
                return response()->json(["estado" => "error", "mensaje" => $exception->getMessage(), "data" => null]);
                DB::rollBack();
            }
        }else{
            return response()->json(["estado" => "error", "mensaje" => "Se encontraron campos vacios que son obligatorios.", "data" => null]);
        }
    }

    /**
    * @OA\Get(
    *     path="/api/v1/usuarios",
    *     summary="Lista de usuarios",    *   
    *     security={
     *         {"bearer": {}}
     *     },
    *     @OA\Response(
    *         response=200,
    *         description="Genera la lista de usuarios."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function usuarios(Request $request)
    {
        $usuarios = User::all();
        return response()->json(["estado" => "success", "mensaje" => "Se cargo con exito la lista.", "data" => $usuarios]);
    }
}
