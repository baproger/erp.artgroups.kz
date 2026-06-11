<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActive
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Ваша учётная запись ожидает активации администратором.');
        }

        return $next($request);
    }
}
