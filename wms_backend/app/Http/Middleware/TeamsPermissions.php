<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TeamsPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
Log::debug('middle ware');
        if(!empty(auth()->user())){
            if(session()->has('branch_id')) {
                setPermissionsTeamId(session('branch_id'));
                $user= auth()->user();
                $user->unsetRelation('roles')->unsetRelation('permissions');
            }
        }

        return $next($request);
    }
}
