<?php


require_once '../src/cms/Image/services/ImageService.php';
require_once '../src/cms/Image/models/Image.php';

class ImageController
{

    public function uploadImage(Request $request, Response $response): Response
    {

        $userId = $request->getDecoded()->user_id ?? null;
        $file = $request->getFile()["image"] ?? null;


        if ($file === null) {
            return ResponseHelper::respondWithError($response, 'No file uploaded', 400);
        }

        $sizeError = ImageService::validateFileSize($file['size']);
        if ($sizeError !== null) {
            return ResponseHelper::respondWithError($response, $sizeError, 400);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $extensionError = ImageService::validateFileExtension($fileExtension);
        if ($extensionError !== null) {
            return ResponseHelper::respondWithError($response, $extensionError, 400);
        }


        $imageId = ImageService::saveImage($file, $userId);
        $image  = ImageService::getById($imageId);

        if ($image === null) {
            return ResponseHelper::respondWithError($response, 'Failed to save image', 500);
        }

        return ResponseHelper::respondWithJson($response, [$image], 200);
    }
    public function getById(Request $request, Response $response): Response
    {

        $imageId = trim($request->getBody()['id']) ?? '';

        $image  = ImageService::getById($imageId);
        if ($image === null) {
            return ResponseHelper::respondWithError($response, 'Image not found', 500);
        }

        return ResponseHelper::respondWithJson($response, ['image' => $image], 200);
    }
}
