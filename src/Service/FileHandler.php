<?php

namespace Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;

class FileHandler {

    public function fileUpload($uploadedFile, $fileName, $uploadpath) {
        
        $sessionData = new Session();
        
        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );
        if (!in_array($mimeType, $allowedTypes)) {
            
            $sessionData->getFlashBag()->add("alert_danger", "Invalid file type, file not uploaded");
            return false;
        }
        
        $fs = new Filesystem();
        $fullFilePath = $uploadpath . $fileName;
        if ($fs->exists(array($fullFilePath . '.docx', $fullFilePath . '.doc', $fullFilePath . '.pdf'))) {
            unlink($fullFilePath . '.*');
        }
        
        $uploadedFile->move($uploadpath, $fullFilePath . '.' . $uploadedFile->guessExtension());
        $sessionData->getFlashBag()->add("alert_success", "File uploaded successfully");
        return true;
    }

}
