<?php

namespace Service;

use Symfony\Component\Filesystem\Filesystem;

class FileHandler {

    public function fileUpload($uploadedFile, $fileName, $uploadpath) {
        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );

        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }
        $fs = new Filesystem();
        $fullFilePath = $uploadpath . $fileName;
        if ($fs->exists(array($fullFilePath . '.docx', $fullFilePath . '.doc', $fullFilePath . '.pdf'))) {
            unlink($fullFilePath . '.*');
        }

        $uploadedFile->move($fullFilePath . '.' . $uploadedFile->getExtension());
        return true;
    }

}
