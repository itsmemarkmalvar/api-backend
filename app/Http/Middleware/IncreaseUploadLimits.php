<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IncreaseUploadLimits
{
    public function handle(Request $request, Closure $next)
    {
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '256M');

        return $next($request);
    }
} 