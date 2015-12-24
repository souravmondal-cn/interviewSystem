<?php

namespace Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
        
        $this->deleteExistingFile($fileName, $uploadpath);
        
        $fullFilePath = $uploadpath . $fileName;
        $uploadedFile->move($uploadpath, $fullFilePath . '.' . $uploadedFile->guessExtension());
        $sessionData->getFlashBag()->add("alert_success", "Resume uploaded successfully");
        return true;
    }
    
    public function deleteExistingFile($fileIdWithoutExtension, $fileFullPath) {
        
        $fs = new FileSystem();

        if ($fs->exists($fileFullPath . $fileIdWithoutExtension . '.docx')) {
            
            unlink($fileFullPath . $fileIdWithoutExtension . '.docx');
        } elseif ($fs->exists($fileFullPath . $fileIdWithoutExtension . '.doc')) {
            
            unlink($fileFullPath . $fileIdWithoutExtension . '.doc');
        } elseif ($fs->exists($fileFullPath . $fileIdWithoutExtension . '.pdf')) {
            
            unlink($fileFullPath . $fileIdWithoutExtension . '.pdf');
        }
        
        return;
    }

    public function downloadExistingFile($filename, $fileFullPath) {
        
        $sessionData = new Session();
        $fs = new Filesystem();

        $file = $fileFullPath . $filename;

        if ($fs->exists($file . '.docx')) {
            
            $fullPath = $file . '.docx';
        } elseif ($fs->exists($file . '.doc')) {
            
            $fullPath = $file . '.doc';
        } elseif ($fs->exists($file . '.pdf')) {
            
            $fullPath = $file . '.pdf';
        } else {
            
            $sessionData->getFlashBag()->add('alert_danger', 'File not found!');
            return false;
        }

        $response = new BinaryFileResponse($fullPath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename,
            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }
}
