<?php

require_once '../src/cms/User/controllers/UserController.php';

require_once '../src/http/Request.php';
require_once '../src/http/Response.php';


return function (Router $app) {


    $userController = new UserController();


    $app->post('/users/register', [], function (Request $request, Response $response) use ($userController) {
        return $userController->register($request, $response);
    });


    $app->post('/users/login', [], function (Request $request, Response $response) use ($userController) {
        return $userController->login($request, $response);
    });


    $app->post('/users/getById', ["JWT"], function (Request $request, Response $response,) use ($userController) {

        return $userController->getUserById($request, $response);
    });

    $app->post('/users/update', ["JWT"], function (Request $request, Response $response,) use ($userController) {

        return $userController->updateUser($request, $response);
    });
};
