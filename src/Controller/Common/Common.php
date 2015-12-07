<?php

namespace Controller\Common;
use Symfony\Component\Filesystem\Filesystem;

class Common {
    
    public function newFileUpload($uploadedFile, $fileName) {

        //Delete previous file of this user
        $fs = new FileSystem();
        if ($fs->exists('uploads/' . $fileName . '.docx')) {
            $fileFullName = $fileName . '.docx';
        } elseif ($fs->exists('uploads/' . $fileName . '.doc')) {
            $fileFullName = $fileName . '.doc';
        } else {
            $fileFullName = $fileName . '.pdf';
        }

        if ($fs->exists('uploads/' . $fileFullName)) {
            unlink('uploads/' . $fileFullName);
        }
        //delete block ends

        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );

        if (!in_array($mimeType, $allowedTypes)) {
            return FALSE;
        }

        $uploadedFile->move('uploads/', $fileName . '.' . $uploadedFile->guessExtension());
        return TRUE;
    }
}

