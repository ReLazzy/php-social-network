<?php
require_once  "../src/utils/DataBase.php";



class User
{
    public ?int $id;
    public string $username;
    public string $email;
    public ?string $password;
    public ?string $birthday;
    public ?string $description;
    public ?int $imageId;
    public ?string $imagePath;


    public function __construct(?int $id, string $username, string $email, ?string $password, ?string $birthday, ?string $description, ?int $imageId)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->birthday = $birthday;
        $this->description = $description;
        $this->imageId = $imageId;
    }
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function create(): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "INSERT INTO `users` (`username`, `email`, `password`) VALUES (:username, :email, :password)";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(":username", $this->username);
            $stmt->bindValue(":email", $this->email);
            $stmt->bindValue(":password", $this->hashPassword($this->password));
            $result = $stmt->execute();
            $stmt->closeCursor();

            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "UPDATE `users` SET";

            $updateValues = [];
            $bindValues = [];


            foreach (get_object_vars($this) as $key => $value) {
                if ($key !== 'id' && $value !== null) {
                    $updateValues[] = "`$key` = :$key";
                    $bindValues[":$key"] = $value;
                }
            }

            if (empty($updateValues)) {
                return true;
            }

            $sql .= " " . implode(", ", $updateValues);
            $sql .= " WHERE id = :id";

            $stmt = $conn->prepare($sql);


            foreach ($bindValues as $param => $value) {
                $stmt->bindValue($param, $value);
            }


            $stmt->bindValue(":id", $this->id);

            $result = $stmt->execute();

            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }

    public function toArray(): array
    {
        return  [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'description' => $this->description,
            'imagePath' => $this->imagePath
        ];
    }


    public function delete(): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $this->id);
            $result = $stmt->execute();
            $stmt->closeCursor();


            return $result;
        } catch (PDOException $e) {
            echo ($e->getMessage());
            return false;
        }
    }
}
