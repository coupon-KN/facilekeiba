<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPC
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mobileなら専用ページを返却
        $user_agent = Request()->header('User-Agent');
        if (strpos($user_agent, 'iPhone') || strpos($user_agent, 'iPod') || strpos($user_agent, 'Android')) {
            return redirect("mobile");
        }
        
        return $next($request);
    }
}
