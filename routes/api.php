<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\BabyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'status' => 'success'
    ]);
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });

    Route::post('google', [GoogleAuthController::class, 'handleGoogleSignIn']);
    Route::post('facebook', [FacebookAuthController::class, 'handleFacebookSignIn']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });
    Route::post('/baby', [BabyController::class, 'store']);
    Route::get('/baby', [BabyController::class, 'show']);
    
    // Add any additional routes needed for the home screen
});
  