<?php

require_once "../src/http/Request.php";
require_once "../src/http/Response.php";

interface MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): Response;
}
