<?php

namespace Service;

use Symfony\Component\Filesystem\Filesystem;

class FileHandler {

    public function fileUpload($uploadedFile, $fileName) {

        $fs = new Filesystem();
        if ($fs->exists(UPLOAD_PATH . $fileName . '.docx')) {
            $fileFullName = $fileName . '.docx';
        } elseif ($fs->exists(UPLOAD_PATH . $fileName . '.doc')) {
            $fileFullName = $fileName . '.doc';
        } else {
            $fileFullName = $fileName . '.pdf';
        }

        if ($fs->exists(UPLOAD_PATH . $fileFullName)) {
            unlink(UPLOAD_PATH . $fileFullName);
        }

        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );

        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        $uploadedFile->move(UPLOAD_PATH, $fileName . '.' . $uploadedFile->guessExtension());
        return true;
    }

}
