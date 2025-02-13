<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatHistoryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
//     // "auth:sanctum" kollar om du har rätt tillgång 
// })->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chat', [ChatHistoryController::class, 'chat']);
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/chat', [ChatbotController::class, 'chat']);

Route::post('/chat', [ChatHistoryController::class, 'chat'])->middleware('auth:sanctum');