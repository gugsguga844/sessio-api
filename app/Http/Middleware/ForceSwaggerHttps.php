<?php

namespace App\Http\Middleware;

use Closure;

class ForceSwaggerHttps
{
    public function handle($request, Closure $next)
    {
        if (
            $request->is('api/documentation') ||
            $request->is('docs') ||
            $request->is('docs/asset/*')
        ) {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        }
        return $next($request);
    }
} 