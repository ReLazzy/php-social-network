<?php

class ImageService
{


    private static $imageFunctions = [
        'jpg' => 'imagecreatefromjpeg',
        'jpeg' => 'imagecreatefromjpeg',
        'png' => 'imagecreatefrompng',
    ];

    public static function getById(int $id): ?Image
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT * FROM images WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $imageData = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($imageData) {
                return new Image($imageData['id'], $imageData['userId'], $imageData['filename'], $imageData['extension'], $imageData['path'], $imageData['hash']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            return null;
        }
    }




    private static function generateResizedImages($image, string $imageDirectory, string $hash, string $extension): void
    {
        $resized128x128 = imagescale($image, 128, 128);
        $resized64x64 = imagescale($image, 64, 64);
        $resized32x32 = imagescale($image, 32, 32);


        imagejpeg($resized128x128, "{$imageDirectory}{$hash}_128x128.{$extension}");
        imagejpeg($resized64x64, "{$imageDirectory}{$hash}_64x64.{$extension}");
        imagejpeg($resized32x32, "{$imageDirectory}{$hash}_32x32.{$extension}");


        imagedestroy($resized128x128);
        imagedestroy($resized64x64);
        imagedestroy($resized32x32);
    }



    public static function validateFileSize(int $size): ?string
    {
        $maxFileSize = 5 * 1024 * 1024;
        if ($size > $maxFileSize) {
            return "File size exceeds the maximum allowed size of 5MB.";
        }
        return null;
    }

    public static function validateFileExtension(string $extension): ?string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            return "File extension is not supported. Allowed extensions are jpg, jpeg, png, gif.";
        }
        return null;
    }



    public static function saveImage(array $file, int $userId): bool|string
    {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $hash = md5_file($file['tmp_name']);


        $oldImage = self::getByHash($hash);

        if ($oldImage && $oldImage->userId === $userId) {
            return $oldImage->id;
        }

        $folder1 = substr($hash, 0, 2);
        $folder2 = substr($hash, 2, 2);
        $folder3 = substr($hash, 4, 2);

        $imageDirectory = "/images/{$folder1}/{$folder2}/{$folder3}/";

        if (!file_exists(".." . $imageDirectory)) {
            mkdir(".." . $imageDirectory, 0777, true);
        }

        $originalImagePath = "{$imageDirectory}{$hash}.{$fileExtension}";

        if (!file_exists(".." . $originalImagePath)) {
            move_uploaded_file($file['tmp_name'], ".." . $originalImagePath);

            if (isset(self::$imageFunctions[$fileExtension]) && function_exists(self::$imageFunctions[$fileExtension])) {
                $imageCreateFunction = self::$imageFunctions[$fileExtension];
                $image = $imageCreateFunction(".." . $originalImagePath);

                self::generateResizedImages($image, ".." . $imageDirectory, $hash, $fileExtension);

                imagedestroy($image);
            }
        }

        $image = new Image(null, $userId, $file['name'], $fileExtension, $originalImagePath, $hash);
        $imageId = $image->save();

        if ($imageId !== null) {
            return $imageId;
        } else {
            return "Failed to save image.";
        }
    }



    private static function getByHash(string $hash): ?Image
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            $sql = "SELECT * FROM images WHERE hash = :hash";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':hash', $hash);
            $stmt->execute();
            $imageData = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($imageData) {
                return new Image($imageData['id'], $imageData['userId'], $imageData['filename'], $imageData['extension'], $imageData['path'], $imageData['hash']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            return null;
        }
    }
}
