<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// ── Ejemplo: cada ruta declara el permiso que exige (403 si el rol no lo tiene) ──
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('convocatorias', [ConvocatoriaController::class, 'index'])->middleware('can:convocatorias:ver');
    // Route::post('convocatorias', [ConvocatoriaController::class, 'store'])->middleware('can:convocatorias:gestionar');
    // Route::post('convocatorias/{convocatoria}/postular', [PostulacionController::class, 'store'])->middleware('can:convocatorias:postular');
    // Route::post('documentos/{documento}/validar', [DocumentoController::class, 'validar'])->middleware('can:documentos:validar');
    // Route::post('evaluaciones/{evaluacion}/finalizar', [EvaluacionController::class, 'finalizar'])->middleware('can:evaluacion:evaluar');
    // Route::post('seleccion/{aspirante}/decidir', [SeleccionController::class, 'decidir'])->middleware('can:seleccion:decidir');
    // Route::apiResource('usuarios', UserController::class)->middleware('can:usuarios:gestionar');
});
