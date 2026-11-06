<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMobileAppToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('mobile.api_token');
        $provided = $request->header('X-App-Token');

        if (!$expected || !$provided || !hash_equals($expected, $provided)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}