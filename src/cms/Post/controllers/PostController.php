<?php



require_once '../src/cms/Post/services/PostService.php';
require_once '../src/cms/Post/models/Post.php';


class PostController

{

    public function createPost(Request $request, Response $response): Response
    {

        $userId = $request->getDecoded()->user_id ?? null;
        if (!$userId) {
            return ResponseHelper::respondWithError($response, 'User ID not found in token', 401);
        }

        $text = trim($request->getBody()['text']) ?? '';
        $imageId = $request->getBody()['imageId'] ?? null;


        $textError = PostService::validateText($text);
        if ($textError !== null) {
            return ResponseHelper::respondWithError($response, $textError, 400);
        }

        $imageError = PostService::validateImageId($imageId);
        if ($imageError !== null) {
            return ResponseHelper::respondWithError($response, $imageError, 400);
        }

        $contentError = PostService::checkPostContent($text, $imageId);
        if ($contentError !== null) {
            return ResponseHelper::respondWithError($response, $contentError, 400);
        }

        $new_post = new Post(null, $userId, $text, $imageId, null, null, null, null);
        $postId = $new_post->create();

        if ($postId) {

            $createdPost = PostService::getById($postId, $userId);
            if ($createdPost) {

                return ResponseHelper::respondWithJson($response, $createdPost->getData(), 200);
            } else {

                return ResponseHelper::respondWithError($response, 'Failed to retrieve created post', 500);
            }
        } else {

            return ResponseHelper::respondWithError($response, 'Failed to create post', 500);
        }
    }



    public function updatePost(Request $request, Response $response): Response
    {
        $postId = $request->getBody()['postId'] ?? null;
        $userId = $request->getDecoded()->user_id ?? null;

        if (!$postId) {
            return ResponseHelper::respondWithError($response, 'Post ID not found in token', 401);
        }

        if (!$userId) {
            return ResponseHelper::respondWithError($response, 'User ID not found in token', 401);
        }

        $currentPost = PostService::getById($postId, $userId);
        if (!$currentPost) {
            return ResponseHelper::respondWithError($response, 'Post does not exist', 401);
        }



        if ($currentPost->userId !== $userId) {
            return ResponseHelper::respondWithError($response, 'You are not the creator of the post', 401);
        }

        $text = trim($request->getBody()['text']) ?? '';
        $imageId = $request->getBody()['imageId'] ?? null;


        $textError = PostService::validateText($text);
        if ($textError !== null) {
            return ResponseHelper::respondWithError($response, $textError, 400);
        }

        $imageError = PostService::validateImageId($imageId);
        if ($imageError !== null) {
            return ResponseHelper::respondWithError($response, $imageError, 400);
        }


        $currentPost->text = $text;
        $currentPost->imageId = $imageId;


        $success = $currentPost->update();

        if ($success) {
            $updatedPost = PostService::getById($postId, $userId);
            if ($updatedPost) {
                return ResponseHelper::respondWithJson($response, $updatedPost->getData(), 200);
            } else {
                return ResponseHelper::respondWithError($response, 'Failed to retrieve updated post', 500);
            }
        } else {

            return ResponseHelper::respondWithError($response, 'Failed to update post', 500);
        }
    }

    public function toggleLike(Request $request, Response $response): Response
    {
        $postId = $request->getBody()['postId'] ?? null;
        $userId = $request->getDecoded()->user_id ?? null;

        if (!$postId) {
            return ResponseHelper::respondWithError($response, 'Post ID is required', 400);
        }
        if (!$userId) {
            return ResponseHelper::respondWithError($response, 'userId ID is required', 400);
        }
        $user = UserService::getById($userId);

        if (!$user) {
            return ResponseHelper::respondWithError($response, 'User does not exist', 400);
        }

        $currentPost = PostService::getById($postId, $userId);
        if (!$currentPost) {
            return ResponseHelper::respondWithError($response, 'Post does not exist', 401);
        }

        $success = PostService::toggleLike($postId, $userId);

        if ($success) {
            return ResponseHelper::respondWithJson($response, ['message' => 'Like toggled successfully'], 200);
        } else {
            return ResponseHelper::respondWithError($response, 'Failed to toggle like', 500);
        }
    }



    public function getFeed(Request $request, Response $response): Response
    {
        $limit = $request->getBody()['limit'] ?? 2;
        $lastPostId = $request->getBody()['lastPostId'] ?? null;
        $userId = $request->getDecoded()->user_id ?? null;
        $currentUser = $request->getBody()['currentUser'] ?? null;

        $feed = PostService::getFeed($userId, $limit, $lastPostId, $currentUser);

        if ($feed !== null) {
            return ResponseHelper::respondWithJson($response, $feed, 200);
        } else {

            return ResponseHelper::respondWithError($response, 'Failed to get Possts', 500);
        }
    }
}
