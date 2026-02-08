<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use App\Util\MobileLoginUtil;
use DateTime;

class CheckLoginToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie("facile-token");
        if(MobileLoginUtil::isLogin($token)){
            return $next($request);
        }

        return response(["id" => "", "name" => ""], 401);
    }
}
