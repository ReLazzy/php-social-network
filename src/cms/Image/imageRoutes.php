<?php

require_once '../src/cms/Image/controllers/ImageController.php';
require_once '../src/http/Request.php';
require_once '../src/http/Response.php';


return function (Router $app) {


    $imageController = new ImageController();


    $app->post('/image/upload', ["JWT"], function (Request $request, Response $response) use ($imageController) {
        return $imageController->uploadImage($request, $response);
    });
    $app->post('/image/getById', ["JWT"], function (Request $request, Response $response) use ($imageController) {
        return $imageController->getById($request, $response);
    });
};
