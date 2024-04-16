<?php

class Post
{
    public ?int $id;
    public int $userId;
    public ?string $text;
    public ?int $imageId;
    public ?string $imagePath;
    public ?int $likes;
    public ?bool $isLike;
    public ?string $name;
    public ?string $createdAt;

    public function __construct(?int $id, int $userId, string $text, ?int $imageId, ?string $imagePath, ?int $likes, ?bool $isLike, ?string $name, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->text = $text;
        $this->likes = $likes;
        $this->isLike = $isLike;
        $this->imageId = $imageId;
        $this->imagePath = $imagePath;
        $this->name = $name;
        $this->createdAt = $createdAt;
    }


    public function create(): ?int
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "INSERT INTO `posts` (`userId`, `text`, `imageId`) VALUES (:userId, :text, :imageId)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':userId', $this->userId);
            $stmt->bindValue(':text',  $this->text);
            $stmt->bindValue(':imageId',  $this->imageId);
            $stmt->execute();
            $postId = $conn->lastInsertId();

            return $postId;
        } catch (PDOException $e) {
            return null;
        }
    }
    public function getData(): array
    {
        return  [
            'id' => $this->id,
            'userId' => $this->userId,
            'text' => $this->text,
            'imageId' => $this->imageId,
            'imagePath' => $this->imagePath,
            'isLike' => $this->isLike,
            'likes' => $this->likes,
            'name' => $this->name,
            'createdAt' => $this->createdAt,

        ];;
    }

    public function update(): bool
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "UPDATE `posts` SET";

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
            return false;
        }
    }
}
