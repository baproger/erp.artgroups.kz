<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return redirect()->route('login')->with('error', 'Ваша учётная запись не активирована.');
        }

        if (! empty($roles) && ! in_array($user->role, $roles)) {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}
