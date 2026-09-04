<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IotIngestionController;
use App\Http\Controllers\Api\IotCameraController;
use App\Http\Controllers\User\UserCareController;
use App\Http\Controllers\User\UserGardenController;
use App\Http\Controllers\User\DiseaseDiagnosisController;
use App\Http\Controllers\User\PestLifecycleController;
use App\Http\Controllers\User\UserSupportController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\ProfileController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// IoT Hardware Ingestion & Control Routes (UC 50, 51)
Route::prefix('iot')->group(function () {
    Route::post('/telemetry', [IotIngestionController::class, 'ingestSensorData']);
    Route::post('/camera/upload', [IotIngestionController::class, 'ingestCameraImage']);
    Route::post('/stations/{stationCode}/command', [IotIngestionController::class, 'sendCommand']);

    // Camera On-Demand Streaming & PTZ APIs
    Route::post('/stations/{stationCode}/camera/stream', [IotCameraController::class, 'startStream']);
    Route::post('/stations/{stationCode}/camera/stop', [IotCameraController::class, 'stopStream']);
    Route::post('/stations/{stationCode}/camera/ptz', [IotCameraController::class, 'ptzControl']);
    Route::post('/stations/{stationCode}/camera/snapshot', [IotCameraController::class, 'captureSnapshot']);
    Route::get('/stations/{stationCode}/camera/status', [IotCameraController::class, 'getStreamStatus']);
});


// Mobile App API Routes (User Role)
Route::prefix('mobile')->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [UserNotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [UserNotificationController::class, 'destroy']);

    Route::post('/settings', [ProfileController::class, 'updateSettings']);
    Route::get('/gardens/weather/{stationId}', [UserGardenController::class, 'weatherHistory']);

    Route::get('/care/categories', [UserCareController::class, 'categories']);
    Route::post('/care/categories', [UserCareController::class, 'storeCategory']);
    Route::get('/care/histories', [UserCareController::class, 'histories']);
    Route::post('/care/histories', [UserCareController::class, 'storeHistory']);
    Route::get('/care/products', [UserCareController::class, 'products']);
    Route::get('/care/processes', [UserCareController::class, 'processes']);

    Route::post('/support', [UserSupportController::class, 'store']);

    Route::post('/ai/diagnose', [DiseaseDiagnosisController::class, 'analyze']);
    Route::post('/ai/pest/check', [PestLifecycleController::class, 'check']);
});
