<?php

namespace App\Http\Middleware;

use Closure;

class APIKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('APP_KEY');
        if ($token != 'wfpkey_2695841035') {
            return response()->json([
                'error' => 'You have an invalid API key',
                'status' => 403
            ], 403);
        }
        return $next($request);
    }
}
