<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

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
    return response()->json([
        'message' => 'Laravel API is running',
        'version' => app()->version()
    ]);
});
