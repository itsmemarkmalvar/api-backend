<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\BabyController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\Auth\FacebookController;

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
    Route::put('/baby', [BabyController::class, 'update']);
    Route::post('/baby/upload-photo', [BabyController::class, 'uploadPhoto']);
    
    // Add any additional routes needed for the home screen
});

// Add new API endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('growth')->group(function () {
        Route::post('/record', [GrowthController::class, 'store']);
        Route::get('/history', [GrowthController::class, 'index']);
        Route::get('/charts', [GrowthController::class, 'charts']);
        Route::get('/percentiles', [GrowthController::class, 'getPercentiles']);
        Route::get('/milestones', [GrowthController::class, 'getMilestones']);
        Route::post('/milestones', [GrowthController::class, 'storeMilestone']);
        Route::put('/milestones/{id}', [GrowthController::class, 'updateMilestone']);
    });
});

Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/auth/facebook', [FacebookController::class, 'handleFacebookCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/auth/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
  