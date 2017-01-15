<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

Log::useFiles('php://stdout', config('app.log_level'));

class CorsMiddleware
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
        Log::debug('corsMiddleware');
        Log::debug('request url : '.$request->url());
        Log::debug('request method : '.$request->getMethod());

        // header('Access-Control-Allow-Origin: *');

        // ALLOW OPTIONS METHOD
        // header('Access-Control-Allow-Origin:  *');
        // header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
        // header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
        
        $headers = [
            'Access-Control-Allow-Origin' =>  '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization'
        ];
        if ($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value)
            $response->header($key, $value);
        return $response;
    }
}
