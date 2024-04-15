<?php

require_once '../src/http/Request.php';
require_once '../src/http/Response.php';
require_once "../src/middleware/MiddlewareInterface.php";

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function post(string $url, array $middlewareKeys, callable $handler): void
    {
        $this->routes["POST"][$url] = [
            'handler' => $handler,
            'middlewareKeys' => $middlewareKeys
        ];
    }

    public function addMiddleware(MiddlewareInterface $middleware, string $key): void
    {
        $this->middleware[$key] = $middleware;
    }

    public function route(): Response
    {


        $method =  $_SERVER['REQUEST_METHOD'];
        $uri    =  $_SERVER['REQUEST_URI'];
        $body   =  json_decode(file_get_contents('php://input'), true) ?? [];
        $files = $_FILES;

        $token      = null;
        $headers    = getallheaders();
        $authHeader = $headers["Authorization"] ?? "";

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }


        $request = new Request($method, $uri, $body, $token, $files);


        $response = new Response();

        $handlerData = $this->routes[$method][$uri] ?? null;

        if (!$handlerData) {
            return new Response(404, 'Not Found');
        }

        $middlewareKeys = $handlerData['middlewareKeys'];

        foreach ($middlewareKeys as $key) {
            $middleware = $this->middleware[$key] ?? null;
            if ($middleware) {
                $response = $middleware->handle($request, new Response(), function ($request) use ($response) {
                    return $response;
                });


                if ($response instanceof Response && $response->getStatusCode() !== 200) {
                    return $response;
                }
            }
        }

        return $handlerData['handler']($request, $response);
    }
}
