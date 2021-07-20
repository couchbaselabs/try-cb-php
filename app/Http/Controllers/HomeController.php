<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function options(Request $request, Response $response)
    {
        $origin = $request->header('origin') ?: $request->url();

        $response->header('Access-Control-Allow-Origin', $origin);
        $response->header('Access-Control-Allow-Headers', 'origin, content-type, accept');
        $response->header('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS, POST, PATCH, DELETE');

        return $response;
    }
}