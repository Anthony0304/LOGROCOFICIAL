<?php

namespace App\Http\Controllers;
/**
 * @OA\Info(
 *    title="API-ANTHONY GANCHOZO",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 */


use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use OpenApi\Annotations as OA;
/**
 * @OA\Schema(
 *     schema="CreateUserRequest",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string"),
 * )
 */
/**
 * @OA\Schema(
 *     schema="LoginUserRequest",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string"),
 * )
 */


/**
 * @OA\Schema(
 *     schema="CandidatoInfo",
 *     @OA\Property(property="descripcion", type="string", description="Descripción del candidato"),
 *     @OA\Property(property="lista", type="string", description="Descripción de la lista a la que pertenece"),
 *     @OA\Property(property="tipo_candidato", type="string", description="Descripción del tipo de candidato")
 * )
 */
/**
 * @OA\Schema(
 *     schema="CandidatoConVotos",
 *     @OA\Property(property="descripcion", type="string", description="Descripción del candidato"),
 *     @OA\Property(property="lista", type="string", description="Lista a la que pertenece el candidato"),
 *     @OA\Property(property="tipo_candidato", type="string", description="Tipo de candidato"),
 *     @OA\Property(property="total_votos", type="integer", description="Total de votos recibidos por el candidato")
 * )
 */
/**
 * @OA\Schema(
 *     schema="IngresarVotoRequest",
 *     required={"idcandidato", "votos"},
 *     @OA\Property(property="idcandidato", type="integer", description="ID del candidato"),
 *     @OA\Property(property="votos", type="integer", description="Cantidad de votos a ingresar")
 * )
 */
/**
 * @OA\Schema(
 *     schema="ActualizarCandidatoRequest",
 *     required={"descripcion", "idlista", "idtipocandidato", "foto"},
 *     @OA\Property(property="descripcion", type="string", description="Descripción del candidato"),
 *     @OA\Property(property="idlista", type="integer", description="ID de la lista a la que pertenece"),
 *     @OA\Property(property="idtipocandidato", type="integer", description="ID del tipo de candidato"),
 *     @OA\Property(property="foto", type="string", description="URL de la foto del candidato")
 * )
 */
/**
 * @OA\Schema(
 *     schema="EliminarCandidatoResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Candidato eliminado exitosamente")
 * )
 */




class AuthController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/auth/register",
 *     summary="Registrar un nuevo usuario",
 *     description="Este endpoint se utiliza para registrar un nuevo usuario.",
 *     operationId="createUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/CreateUserRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario registrado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Usuario registrado exitosamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Existen campos vacios',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
/**
 * @OA\Post(
 *     path="/api/auth/login",
 *     summary="Iniciar sesión",
 *     description="Este endpoint se utiliza para que un usuario inicie sesión en la aplicación.",
 *     operationId="loginUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/LoginUserRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Inicio de sesión exitoso",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="expires_in", type="integer"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credenciales inválidas",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Unauthenticated"),
 *             @OA\Property(property="message", type="string", example="Credenciales inválidas"),
 *         )
 *     )
 * )
 */

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
 * @OA\Get(
 *     path="/api/auth/listado-candidatos",
 *     summary="Obtener listado de candidatos, lista y tipo de candidato",
 *     description="Este endpoint se utiliza para obtener un listado de candidatos junto con la lista a la que pertenecen y el tipo de candidato.",
 *     operationId="getListadoCandidatos",
 *     @OA\Response(
 *         response=200,
 *         description="Listado de candidatos obtenido exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="Listado de Candidatos", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="descripcion", type="string"),
 *                     @OA\Property(property="lista", type="string"),
 *                     @OA\Property(property="tipo_candidato", type="string")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 */
    public function getListadoCandidatos()
{
    $listadoCandidatos = DB::table('candidatos')
        ->join('listas', 'candidatos.idlista', '=', 'listas.id')
        ->join('tipocandidatos', 'candidatos.idtipocandidato', '=', 'tipocandidatos.id')
        ->select('candidatos.descripcion as candidato', 'listas.descripcion as lista', 'tipocandidatos.descripcion as tipo_candidato')
        ->where('candidatos.estado', true)
        ->get();

    return response()->json([
        "Listado de Candidatos" => $listadoCandidatos,
    ]);
}
/**
 * @OA\Get(
 *     path="/api/auth/candidatos/con-votos",
 *     summary="Obtener listado de candidatos con sus votos",
 *     description="Este endpoint se utiliza para obtener un listado de candidatos junto con la cantidad total de votos que han recibido.",
 *     operationId="getListadoCandidatosConVotos",
 *     @OA\Response(
 *         response=200,
 *         description="Listado de candidatos con votos obtenido exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="Listado de Candidatos con Votos", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="descripcion", type="string", description="Descripción del candidato"),
 *                     @OA\Property(property="lista", type="string", description="Lista a la que pertenece el candidato"),
 *                     @OA\Property(property="tipo_candidato", type="string", description="Tipo de candidato"),
 *                     @OA\Property(property="total_votos", type="integer", description="Total de votos recibidos por el candidato")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 */
public function getListadoCandidatosConVotos()
{
    $listadoCandidatosVotos = DB::table('candidatos')
        ->join('listas', 'candidatos.idlista', '=', 'listas.id')
        ->leftJoin('votos', 'candidatos.id', '=', 'votos.idcandidato')
        ->select('listas.descripcion as lista', 'candidatos.descripcion as candidato', DB::raw('SUM(votos.votos) as total_votos'))
        ->groupBy('listas.descripcion', 'candidatos.descripcion')
        ->where('candidatos.estado', true)
        ->orderBy('listas.descripcion')
        ->orderBy('total_votos', 'desc')
        ->get();

    return response()->json([
        "Listado de Candidatos con Votos" => $listadoCandidatosVotos,
    ]);
}
/**
 * @OA\Post(
 *     path="/api/ingresar-voto",
 *     summary="Ingresar un nuevo voto",
 *     description="Este endpoint permite ingresar un nuevo voto para un candidato específico.",
 *     operationId="ingresarVoto",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/IngresarVotoRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Voto ingresado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Voto ingresado exitosamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error en la solicitud",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

public function ingresarVoto(Request $request)
{
    $validator = Validator::make($request->all(), [
        'idcandidato' => 'required|exists:candidatos,id',
        'votos' => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => 'Error en los datos ingresados.',
            'errors' => $validator->errors(),
        ], 400);
    }

    $votos = $request->input('votos');
    $idcandidato = $request->input('idcandidato');

    // Insertar el voto en la base de datos
    DB::table('votos')->insert([
        'votos' => $votos,
        'user_id' => Auth::user()->id,
        'idcandidato' => $idcandidato,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Voto registrado exitosamente.',
    ]);
}
/**
 * @OA\Put(
 *     path="/api/actualizar-candidato/{id}",
 *     summary="Actualizar información de un candidato",
 *     description="Este endpoint permite actualizar la información de un candidato específico.",
 *     operationId="actualizarCandidato",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del candidato",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ActualizarCandidatoRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Información del candidato actualizada exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Información del candidato actualizada exitosamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error en la solicitud",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Candidato no encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

public function actualizarCandidato(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'descripcion' => 'required|string|max:200',
        'idlista' => 'required|exists:listas,id',
        'idtipocandidato' => 'required|exists:tipocandidatos,id',
        'foto' => 'nullable|string|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => 'Error en los datos ingresados.',
            'errors' => $validator->errors(),
        ], 400);
    }

    $descripcion = $request->input('descripcion');
    $idlista = $request->input('idlista');
    $idtipocandidato = $request->input('idtipocandidato');
    $foto = $request->input('foto');

    // Actualizar los datos del candidato en la base de datos
    DB::table('candidatos')
        ->where('id', $id)
        ->update([
            'descripcion' => $descripcion,
            'idlista' => $idlista,
            'idtipocandidato' => $idtipocandidato,
            'foto' => $foto,
            'updated_at' => now(),
        ]);

    return response()->json([
        'message' => 'Información del candidato actualizada exitosamente.',
    ]);
}
/**
 * @OA\Delete(
 *     path="/api/eliminar-candidato/{id}",
 *     summary="Eliminar un candidato",
 *     description="Este endpoint permite eliminar un candidato específico.",
 *     operationId="eliminarCandidato",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del candidato",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Candidato eliminado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Candidato eliminado exitosamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Candidato no encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

public function eliminarCandidato($id)
{
    // Verificar si el candidato existe
    $candidato = DB::table('candidatos')->find($id);

    if (!$candidato) {
        return response()->json([
            'error' => true,
            'message' => 'El candidato no existe.',
        ], 404);
    }

    // Eliminar el candidato de la tabla
    DB::table('candidatos')->where('id', $id)->delete();

    return response()->json([
        'message' => 'Candidato eliminado exitosamente.',
    ]);
}


}
