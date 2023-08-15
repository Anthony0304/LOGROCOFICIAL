<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use L5Swagger\Facades\Swagger;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::group(['prefix' => 'api'], function () {
    // Otras rutas de la API...



    // Ruta para generar la documentación de Swagger/OpenAPI
    Route::get('/docs', function () {
        return view('swagger.index'); // Puedes personalizar esta vista si lo deseas
    });

    // Ruta para acceder al archivo JSON de la documentación de Swagger/OpenAPI
    Route::get('/docs-json', function () {
        return response()->json(Swagger::getRawDocs()); // Devuelve el archivo JSON generado por Swagger
    });
});
Route::group(['middleware'=>["auth:sanctum"]],function() {
    Route::get('/auth/listado-candidatos', [AuthController::class, 'getListadoCandidatos']);
    Route::get('/auth/listado-votos-por-candidato', [AuthController::class, 'getListadoVotosPorCandidato']);
    Route::post('/auth/ingresar-votos', [AuthController::class, 'ingresarVotos']);
    Route::put('/auth/actualizar-candidato/{id}', [AuthController::class, 'updateCandidato']);
    Route::delete('/auth/eliminar-candidato/{id}', [AuthController::class, 'eliminarCandidato']);

});
