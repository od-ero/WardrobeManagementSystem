<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use function Pest\Laravel\get;

class EnsureBranchId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        //$branch_id = getPermissionsTeamId();
        if(!empty(auth()->user())) {
            $user = auth()->user();
           // if (!$branch_id) {

                if (session()->has('branch_id') && session()->has('organization_id')) {

                    setPermissionsTeamId(session('branch_id'));
                    $user = Auth::user();
                    $user->unsetRelation('roles')->unsetRelation('permissions');

                } else {
                    return response()->json([
                        'message' => 'Branch is required',
                        'errors' => ['Select a branch or Organization'],
                    ], 403);
                }
            //}
        }
        return $next($request);
    }
}
