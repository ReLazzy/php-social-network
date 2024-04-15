<?php
require_once  "../src/utils/DataBase.php";



class UserService
{


    public static function getByUsername(string $username): ?User
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT u.*, i.path AS image_path
                    FROM users u
                    LEFT JOIN images i ON u.imageId = i.id
                    WHERE u.username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($user) {
                $userObj = new User($user['id'], $user['username'], $user['email'], $user['password'], $user['birthday'], $user['description'], $user['id']);
                $userObj->imagePath = $user['image_path'];
                return $userObj;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            echo ($e->getMessage());
            return null;
        }
    }

    public static function getById(int $userId): ?User
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT u.*, i.path AS image_path
                    FROM `users` u
                    LEFT JOIN `images` i ON u.imageId = i.id
                    WHERE u.id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($user) {
                $userObj = new User($user['id'], $user['username'], $user['email'], null, $user['birthday'], $user['description'], $user['id']);
                $userObj->imagePath = $user['image_path'];
                return $userObj;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            echo ($e->getMessage());
            return null;
        }
    }


    public static function getUsersByIds(array $userIds): array
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql = `SELECT u.*, i.path AS image_path 
                FROM users u
                LEFT JOIN images i ON u.imageId = i.id
                WHERE u.id IN ($placeholders)`;
            $stmt = $conn->prepare($sql);
            $stmt->execute($userIds);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();


            $userObjects = [];
            foreach ($users as $user) {
                $userObjects[] = new User($user['id'], $user['username'], $user['email'], $user['password'], $user['birthday'], $user['description'], $user['imageId']);
            }
            return $userObjects;
        } catch (PDOException $e) {
            echo ($e->getMessage());
            return [];
        }
    }



    public static function isPasswordStrongEnough(string $password): ?string
    {

        if (strlen($password) < 8) {
            return "The password must be at least 8 characters long.";
        }


        if (!preg_match('/[0-9]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
            return "Пароль должен содержать хотя бы одну цифру, одну заглавную и одну строчную букву.";
        }

        return null;
    }

    public static function isEmailValid(string $email): bool
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) === false);
    }


    public static function isEmailTaken(string $email): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            $stmt->closeCursor();

            return $count > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    public static function isUsernameTaken(string $username): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            $stmt->closeCursor();

            return $count > 0;
        } catch (PDOException $e) {
            return false;
        }
    }



    public static function getList(int $limit, int $offset): array
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT * FROM users LIMIT :limit OFFSET :offset";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();


            $userObjects = [];
            foreach ($users as $user) {
                $userObjects[] = new User($user['id'], $user['username'], $user['email'], $user['password'], $user['birthday'], $user['description'], $user['imageId']);
            }
            return $userObjects;
        } catch (PDOException $e) {

            return [];
        }
    }
}
