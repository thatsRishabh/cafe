<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Employee
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
        if(!\Auth::check()){
            return response(prepareResult(true, [], trans('translate.permission_not_defined')), config('httpcodes.forbidden'));
        }
        if(!$request->user()->role_id == '3'){
            $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
                return response(prepareResult(true, [], trans('translate.permission_not_defined')), config('httpcodes.forbidden'));
            });
        }
        return $next($request);
    }
}
