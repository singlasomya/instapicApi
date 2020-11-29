<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Foundation\Applicaion;

class AddHeaders
{
    public function handle($request, Closure $next)
    {

    	$cors = "*";

        $response = $next($request);
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return $response;
        }
        if($request->getMethod() === 'POST' || $request->getMethod() === 'GET'){
        	$response->header('Access-Control-Allow-Origin', $cors);
        }else if($request->getMethod() === 'OPTIONS'){
        	$response->header('Access-Control-Allow-Origin', $cors);
            $origin = $request->header('ORIGIN', '*');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
            header('Access-Control-Max-Age: 600');
            header('Cache-Control: public');
            header('Access-Control-Allow-Headers: Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers, cache-control, token, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie, X-XSRF-TOKEN, x-csrf-token, access-control-allow-credentials, access-control-allow-origin');
        }

        return $response;
    }
}
