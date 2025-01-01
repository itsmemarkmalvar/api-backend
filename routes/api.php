<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\BabyController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\SleepController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineScheduleController;
use App\Http\Controllers\MedicineLogController;

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
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
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
    
    // Feeding routes
    Route::prefix('feeding')->group(function () {
        Route::get('/', [FeedingController::class, 'index']);
        Route::post('/', [FeedingController::class, 'store']);
        Route::get('/stats', [FeedingController::class, 'stats']);
        Route::get('/{id}', [FeedingController::class, 'show']);
        Route::put('/{id}', [FeedingController::class, 'update']);
        Route::delete('/{id}', [FeedingController::class, 'destroy']);
    });
    
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

// Milestone routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/milestones/{babyId}', [MilestoneController::class, 'index']);
    Route::post('/milestones/{babyId}/{milestoneId}/toggle', [MilestoneController::class, 'toggle']);
    Route::post('/milestones/{babyId}/initialize', [MilestoneController::class, 'initializeMilestones']);
});

// Sleep Tracking Routes
Route::middleware(['auth:sanctum', 'verified', \App\Http\Middleware\AttachBabyToRequest::class])->group(function () {
    Route::get('/sleep', [SleepController::class, 'index']);
    Route::post('/sleep', [SleepController::class, 'store']);
    Route::get('/sleep/stats', [SleepController::class, 'stats']);
    Route::get('/sleep/{id}', [SleepController::class, 'show']);
    Route::put('/sleep/{id}', [SleepController::class, 'update']);
    Route::delete('/sleep/{id}', [SleepController::class, 'destroy']);
});

// Medicine routes
Route::middleware(['auth:sanctum', 'verified', 'attach.baby'])->group(function () {
    // Medicine CRUD
    Route::get('/medicines', [MedicineController::class, 'index']);
    Route::post('/medicines', [MedicineController::class, 'store']);
    Route::get('/medicines/{id}', [MedicineController::class, 'show']);
    Route::put('/medicines/{id}', [MedicineController::class, 'update']);
    Route::delete('/medicines/{id}', [MedicineController::class, 'destroy']);

    // Medicine Schedules
    Route::get('/medicines/{medicineId}/schedules', [MedicineScheduleController::class, 'index']);
    Route::post('/medicines/{medicineId}/schedules', [MedicineScheduleController::class, 'store']);
    Route::put('/medicines/{medicineId}/schedules/{id}', [MedicineScheduleController::class, 'update']);
    Route::delete('/medicines/{medicineId}/schedules/{id}', [MedicineScheduleController::class, 'destroy']);
    Route::get('/medicines/schedules/upcoming', [MedicineScheduleController::class, 'getUpcoming']);

    // Medicine Logs
    Route::get('/medicines/{medicineId}/logs', [MedicineLogController::class, 'index']);
    Route::post('/medicines/{medicineId}/logs', [MedicineLogController::class, 'store']);
    Route::put('/medicines/{medicineId}/logs/{id}', [MedicineLogController::class, 'update']);
    Route::delete('/medicines/{medicineId}/logs/{id}', [MedicineLogController::class, 'destroy']);
    Route::get('/medicines/{medicineId}/stats', [MedicineLogController::class, 'getStats']);
});
  