<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Hash;

// Enhanced debug route
Route::get('/debug-routes', function () {
    $routes = collect(\Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'middleware' => $route->middleware(),
        ];
    });
    
    // Check if API routes file exists and get its contents
    $apiRoutesPath = base_path('routes/api.php');
    $apiRoutesContent = File::exists($apiRoutesPath) ? File::get($apiRoutesPath) : 'File not found';
    
    return response()->json([
        'routes' => $routes,
        'request_info' => [
            'path' => request()->path(),
            'url' => request()->url(),
            'full_url' => request()->fullUrl(),
            'method' => request()->method(),
            'is_api_request' => request()->is('api/*'),
            'headers' => request()->headers->all(),
        ],
        'debug_info' => [
            'base_path' => base_path(),
            'api_routes_exists' => File::exists($apiRoutesPath),
            'api_routes_content' => $apiRoutesContent,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]
    ]);
});

Route::get('/test-web', function () {
    return ['message' => 'Web route is working'];
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy-policy', [PolicyController::class, 'show']);
Route::get('/terms', [TermsController::class, 'show']);

// Add this temporary test route
Route::get('/test-all-routes', function () {
    return response()->json([
        'status' => 'success',
        'routes' => [
            'home' => url('/'),
            'privacy' => url('/privacy-policy'),
            'terms' => url('/terms'),
        ],
        'views_exist' => [
            'privacy_policy' => view()->exists('privacy-policy'),
            'terms' => view()->exists('terms'),
        ],
        'controllers_exist' => [
            'policy_controller' => class_exists('App\Http\Controllers\PolicyController'),
            'terms_controller' => class_exists('App\Http\Controllers\TermsController'),
        ]
    ]);
});

// Password Reset Routes
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])
    ->name('password.update');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

// Add a route to handle direct token verification
Route::get('password/verify/{token}', function($token) {
    return response()->json([
        'valid' => \DB::table('password_reset_tokens')->where('token', Hash::make($token))->exists()
    ]);
});
