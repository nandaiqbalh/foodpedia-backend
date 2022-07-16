<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        // cek apakah user sudah login, dan apakah yg login itu rolesnya admin
        if (Auth::user() && Auth::user()->roles == 'ADMIN') {

            // kalau iya, maka akan lanjutkan requestnya
            return $next($request);
        }

        // kalau tidak, maka akan redirect ke home page
        return redirect('/');
    }
}
