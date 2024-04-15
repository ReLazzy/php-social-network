<?php

class ResponseHelper
{
    public static function respondWithError(Response $response, string $error, int $statusCode): Response
    {
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode(['error' => $error]));
        return $response;
    }

    public static function respondWithMessage(Response $response, string $message, int $statusCode): Response
    {
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode(['message' => $message]));
        return $response;
    }

    public static function respondWithJson(Response $response, array $data, int $statusCode): Response
    {
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}
