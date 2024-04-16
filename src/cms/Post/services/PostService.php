<?php

class PostService
{

    public static function toggleLike(int $postId, int $userId): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {

            $sql = "INSERT INTO likes (postId, userId) VALUES (:postId, :userId)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':postId', $postId);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            try {
                $sql = "DELETE FROM likes WHERE postId = :postId AND userId = :userId";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':postId', $postId);
                $stmt->bindValue(':userId', $userId);
                $stmt->execute();

                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    }


    public static function validateText(string $text): ?string
    {

        if (mb_strlen($text) > 500) {
            return 'Text exceeds maximum length of 500 characters';
        }
        return null;
    }

    public static function validateImageId(?int $imageId): ?string
    {
        if (!$imageId) {
            return null;
        }
        $image = ImageService::getById($imageId);
        if (!$image) {
            return 'The image does not exist';
        }
        return null;
    }

    public static function checkPostContent(string $text, ?int $imageId): ?string
    {
        if (empty($text) && $imageId === null) {
            return 'The post must contain text or image';
        }
        return null;
    }


    public static function createPost(int $userId, string $text, ?int $imageId): ?int
    {
        if (!self::validateText($text)) {
            return null;
        }

        if (!self::validateImageId($imageId)) {
            return null;
        }

        if (!self::checkPostContent($text, $imageId)) {
            return null;
        }

        $post = new Post(null, $userId, $text, $imageId, null, null, null, null);
        return $post->create();
    }



    public static function getById(int $postId, int $userId): ?Post
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT p.*, 
                        i.path AS image_path, u.username AS 'name',
                        (SELECT COUNT(*) FROM likes WHERE postId = p.id AND userId =:userId) as isLike
                    FROM `posts` p
                    LEFT JOIN `images` i ON p.imageId = i.id
                    LEFT JOIN `users` u ON p.userId = u.id
                    WHERE p.id = :postId
                    GROUP BY p.id";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);

            $stmt->execute();
            $postData = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$postData) {
                return null;
            }

            $post = new Post(
                $postData['id'],
                $postData['userId'],
                $postData['text'],
                $postData['imageId'],
                $postData['image_path'],
                $postData['likes'],
                $postData['isLike'],
                $postData['name']
            );

            return $post;
        } catch (PDOException $e) {

            return null;
        }
    }
    public static function getFeed(int $userId, int $limit, ?int $lastPostId, ?int $currentUser): array
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT p.*, 
                    i.path AS image_path,
                    u.username AS 'name',
                    (SELECT COUNT(*) FROM likes WHERE postId = p.id AND userId = :userId) as isLike
                    FROM `posts` p LEFT JOIN `images` i ON p.imageId = i.id LEFT JOIN `users` u ON p.userId = u.id
                WHERE p.id < :lastPostId OR :lastPostId IS NULL";


            if ($currentUser) {
                $sql .= " AND p.userId = :currentUser";
            }

            $sql .= " ORDER BY p.createdAt DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':lastPostId', $lastPostId, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            if ($currentUser) {
                $stmt->bindValue(':currentUser', $currentUser, PDO::PARAM_INT);
            }

            $stmt->execute();

            $postsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $posts = [];
            foreach ($postsData as $postData) {

                $post = new Post(
                    $postData['id'],
                    $postData['userId'],
                    $postData['text'],
                    $postData['imageId'],
                    $postData['image_path'],
                    $postData['likes'],
                    $postData['isLike'],
                    $postData['name'],
                    $postData['createdAt'],
                );
                $posts[] = $post->getData();
            }

            return $posts;
        } catch (PDOException $e) {
            return null;
        }
    }
}
