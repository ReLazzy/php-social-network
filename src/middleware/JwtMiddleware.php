<?php

use Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require_once "../src/http/Request.php";
require_once "../src/http/Response.php";
require_once "../src/middleware/MiddlewareInterface.php";





class JwtMiddleware implements MiddlewareInterface
{
    private static $secretKey = "RustamSVAZ";

    public function handle(Request $request, Response $response, callable $next): Response
    {
        try {
            $token = $request->getToken();

            if ($token === null) {
                $response->setStatusCode(401);
                $response->setBody(json_encode(['error' => 'Unauthorized']));
                return $response;
            }

            try {
                $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
                $request->setDecoded($decoded);
            } catch (\Exception $e) {
                $response->setStatusCode(401);
                $response->setBody(json_encode(['error' => 'Invalid token']));
                return $response;
            }
            return $next($request, $response);
        } catch (\Exception $e) {

            return  ResponseHelper::respondWithError($response, $e->getMessage(), 500);
        }
    }


    public static function generateJwtToken(User $user): string
    {
        $payload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }
}
