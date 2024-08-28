<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAksesId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $aksesId): Response
    {
        $user = Auth::user();

        if ($user && $user->akses()->where('akses.id', $aksesId)->exists()) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
