<?php

namespace App\Http\Middleware;

use Closure;

class CheckDelegacy
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
        if(auth()->check())
        {
            if (auth()->user()->role == config('setting.roles.admin_department') || auth()->user()->delegacy == config('setting.delegacy.department_admin'))
            {
                return $next($request);
            } else {
                return redirect()->route('not-found');
            }
        }

        return redirect()->route('login.index');
    }
}
