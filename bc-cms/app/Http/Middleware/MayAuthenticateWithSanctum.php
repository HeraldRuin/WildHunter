<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MayAuthenticateWithSanctum
{
    public function handle(Request $request, \Closure $next)
    {
        auth()->shouldUse('sanctum');

        return $next($request);
    }
}
