<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiJsonResponseOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $content = $response->getOriginalContent();
        if ($response->getStatusCode() == 500) {
            $content = [
                'code' => $response->getStatusCode(),
                'data' => 'Server Error'
            ];
        }
        
        return $response->setContent(json_encode($content));
    }
}
