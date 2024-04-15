<?php

class Image

{
    public ?int $id;
    public int $userId;
    public string $filename;
    public string $extension;
    public string $path;
    public string $hash;


    public function __construct(?int $id, int $userId, ?string $filename, string $extension, string $path, string $hash)
    {

        $this->id = $id;
        $this->userId = $userId;
        $this->filename = $filename ?? "";
        $this->extension = $extension;
        $this->path = $path;
        $this->hash = $hash;
    }



    public function save(): ?int
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "INSERT INTO `images` (userId, filename, extension, path, hash) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);


        $stmt->bindValue(1, $this->userId);
        $stmt->bindValue(2, $this->filename);
        $stmt->bindValue(3, $this->extension);
        $stmt->bindValue(4, $this->path);
        $stmt->bindValue(5, $this->hash);

        $result = $stmt->execute();

        $lastInsertId = $conn->lastInsertId();

        $stmt->closeCursor();

        return $result ? (int)$lastInsertId : null;
    }
}
