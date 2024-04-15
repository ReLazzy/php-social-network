<?php

require_once '../src/cms/Post/controllers/PostController.php';
require_once '../src/http/Request.php';
require_once '../src/http/Response.php';


return function (Router $app) {


    $postController = new PostController();


    $app->post('/posts/create', ["JWT"], function (Request $request, Response $response) use ($postController) {
        return $postController->createPost($request, $response);
    });


    $app->post('/posts/update', ["JWT"], function (Request $request, Response $response) use ($postController) {
        return $postController->updatePost($request, $response);
    });
    $app->post('/posts/like', ["JWT"], function (Request $request, Response $response) use ($postController) {
        return $postController->toggleLike($request, $response);
    });


    $app->post('/posts/feed', ["JWT"], function (Request $request, Response $response,) use ($postController) {

        return $postController->getFeed($request, $response);
    });
};
