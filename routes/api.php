<?php

use App\Http\Controllers\FlowController;
use App\Http\Controllers\SensorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeRedController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('node-red')->group(function () {
    Route::post('/mqtt', [NodeRedController::class, 'addMqttSensor']);
    Route::post('/http', [NodeRedController::class, 'addHttpSensor']);
    Route::post('/ws', [NodeRedController::class, 'addWsSensor']);
});

Route::get('/flows', [FlowController::class, 'index']);

Route::prefix('sensors')->group(function () {
    Route::get('/', [SensorController::class, 'index']);
    Route::get('{id}', [SensorController::class, 'show']);
    Route::post('/', [SensorController::class, 'store']);
    Route::put('{id}', [SensorController::class, 'update']);
    Route::delete('{id}', [SensorController::class, 'destroy']);
    Route::get('/flow/{id}', [SensorController::class, 'getByflowId']);
});