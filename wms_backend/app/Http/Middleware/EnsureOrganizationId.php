<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!empty(auth()->user())) {
          $user =auth()->user();
          if($user->special_access == 0){
              if ($user->organization_id) {
                  session()->put(['organization_id' => $user->organization_id]);
              }
              else{
                  return response()->json([
                      'message' => 'Organization is required',
                      'errors' => ['Select  Organization'],
                  ], 403);
              }
          }else {
              if (!session()->has('organization_id')) {
                  return response()->json([
                      'message' => 'Organization is required',
                      'errors' => ['Select  Organization'],
                  ], 403);
              }
          }
        }
        return $next($request);
    }
}
