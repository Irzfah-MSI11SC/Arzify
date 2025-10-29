<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KasirAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('kasir_id')) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        return $next($request);
    }
}
