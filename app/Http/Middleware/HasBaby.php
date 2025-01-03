<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Baby;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HasBaby
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!auth()->check()) {
                return new JsonResponse([
                    'message' => 'Unauthenticated',
                    'error' => 'AUTH_REQUIRED'
                ], 401);
            }

            $baby = Baby::where('user_id', auth()->id())->first();
            
            if (!$baby) {
                return new JsonResponse([
                    'message' => 'No baby profile found. Please create a baby profile first.',
                    'error' => 'BABY_NOT_FOUND'
                ], 404);
            }

            // Add baby to request for easy access in controllers
            $request->merge(['baby' => $baby]);
            
            return $next($request);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error checking baby profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 