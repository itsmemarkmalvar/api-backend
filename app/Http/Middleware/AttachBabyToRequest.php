<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Baby;

class AttachBabyToRequest
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $baby = Baby::where('user_id', $user->id)->first();
        
        if (!$baby) {
            return response()->json(['error' => 'No baby found for this user'], 404);
        }

        $request->merge(['baby' => $baby]);
        
        return $next($request);
    }
} 