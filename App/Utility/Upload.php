<?php

namespace App\Utility;

class Upload {


    public static function uploadFile($file, $fileName, callable $moveUploadedFile = null, $uploadDirectory = null)
    {
        $fileExtension = self::validateFile($file);
        $pictureName = basename($fileName . '.'. $fileExtension);
        $uploadDirectory = $uploadDirectory ?: getcwd() . '/storage';
        $uploadPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pictureName;
        $moveUploadedFile = $moveUploadedFile ?: 'move_uploaded_file';
        $didUpload = $moveUploadedFile($file['tmp_name'], $uploadPath);

        if ($didUpload) {
            return $pictureName;
        } else {
            throw new \Exception("An error occurred. Please contact the administrator.");
        }
    }

    public static function validateFile($file)
    {
        $fileExtensionsAllowed = ['jpeg', 'jpg', 'png'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $fileExtensionsAllowed, true)) {
            throw new \Exception("This file extension is not allowed. Please upload a JPEG or PNG file");
        }

        if (($file['size'] ?? 0) > 4000000) {
            throw new \Exception("File exceeds maximum size (4MB)");
        }

        return $fileExtension;
    }
}
