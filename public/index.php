<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}


require __DIR__ . "/../vendor/autoload.php";

require_once "../src/middleware/JwtMiddleware.php";
require_once "../src/Router/Routing.php";
require_once "../src/http/Response.php";


$usersRoutes = require_once '../src/cms/User/userRoutes.php';
$postsRoutes = require_once '../src/cms/Post/postRoutes.php';
$imagesRoutes = require_once '../src/cms/Image/imageRoutes.php';

$router = new Router();

$jwtMiddleware = new JwtMiddleware();

$router->addMiddleware($jwtMiddleware, 'JWT');

$usersRoutes($router);
$postsRoutes($router);
$imagesRoutes($router);

$response  = $router->route();


http_response_code($response->getStatusCode());
header('Content-Type: application/json');
echo ($response->getBody());
