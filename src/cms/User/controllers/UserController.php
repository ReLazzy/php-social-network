<?php

require_once "../src/cms/User/models/User.php";
require_once "../src/middleware/JwtMiddleware.php";
require_once "../src/utils/ResponseHelper.php";
require_once '../src/cms/User/services/UserService.php';

class UserController
{

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getBody();

        if (!isset($data['username'], $data['email'], $data['password'])) {
            return  ResponseHelper::respondWithError($response, 'Missing username, email, or password', 400);
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];



        $passwordStrengthError = UserService::isPasswordStrongEnough($password);
        if ($passwordStrengthError !== null) {
            return  ResponseHelper::respondWithError($response, $passwordStrengthError, 400);
        }

        if (UserService::isUsernameTaken($username)) {
            return  ResponseHelper::respondWithError($response, 'Username already taken', 400);
        }

        if (UserService::isEmailValid($email)) {
            return  ResponseHelper::respondWithError($response, 'Wrong email', 400);
        }

        if (UserService::isEmailTaken($email)) {
            return  ResponseHelper::respondWithError($response, 'Email already taken', 400);
        }

        $user = new User(null, $username, $email, $password, null, null, null);

        $user->create();

        return  ResponseHelper::respondWithMessage($response, 'User registered successfully', 200);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getBody();

        if (!isset($data['username'], $data['password'])) {
            return  ResponseHelper::respondWithError($response, 'Missing username or password', 400);
        }

        $username = $data['username'];
        $password = $data['password'];

        $user = UserService::getByUsername($username);

        if (!$user || !password_verify($password, $user->password)) {
            return  ResponseHelper::respondWithError($response, 'Invalid username or password', 401);
        }

        $jwtToken = JwtMiddleware::generateJwtToken($user);

        return  ResponseHelper::respondWithJson($response, ['token' => $jwtToken, "id" => $user->id], 200);
    }


    public function updateUser(Request $request, Response $response): Response
    {
        $userId = $request->getDecoded()->user_id ?? null;
        $user = UserService::getById($userId);

        if (!$user) {
            return ResponseHelper::respondWithError($response, 'User not found', 404);
        }
        $updateData = $request->getBody();
        if (empty($updateData)) {
            return ResponseHelper::respondWithError($response, 'Update data is missing', 400);
        }

        $birthday = $updateData["birthday"] ?? null;
        $description = $updateData["description"] ?? null;



        $imageId = $updateData["imageId"] ?? null;

        $textError = UserService::validateDescription($description);
        if ($textError !== null) {
            return ResponseHelper::respondWithError($response, $textError, 400);
        }

        $updateUser = new User($user->id, $user->username, $user->email, null, $birthday, $description, $imageId);
        if ($updateUser->update()) {
            $user = UserService::getById($userId);
            return ResponseHelper::respondWithJson($response, $user->toArray(), 200);
        } else {
            return ResponseHelper::respondWithError($response, 'Failed to update user', 500);
        }
    }

    public function getUserById(Request $request, Response $response): Response
    {

        $body = $request->getBody();

        $userId = $body['id'] ?? null;
        if ($userId === null) {
            return ResponseHelper::respondWithError($response, 'User ID is missing', 400);
        }



        $user = UserService::getById($userId);

        if ($user === null) {
            return ResponseHelper::respondWithError($response, 'User not found', 404);
        }



        return ResponseHelper::respondWithJson($response, $user->toArray(), 200);
    }
}
