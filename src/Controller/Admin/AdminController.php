<?php

namespace Controller\Admin;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminController {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function loginAdmin() {
        return $this->app['twig']->render('admin/adminlogin.twig');
    }

    public function doLoginAdmin(Request $request) {
        $postedLogInData = $request->request->all();

        $sessionData = $this->app['session'];

        $loginDetails = array(
            'email' => $postedLogInData['loginEmail'],
            'password' => md5($postedLogInData['loginPassword']),
            'is_admin' => '1'
        );

        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\User')->findBy($loginDetails);

        if (empty($loginInfo)) {
            $sessionData->getFlashBag()->add('alert_danger', 'Sorry, email id and password does not matched!');
            return $this->app->redirect("/admin");
        }

        $sessionData->set('loginAdminSession', true);
        $sessionData->set('loginAdminEmail', $loginDetails['email']);
        $sessionData->getFlashBag()->add('alert_success', 'Welcome to admin panel');
        return $this->app->redirect('/adminpanel');
    }

    public function adminPanel() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app['twig']->render('admin/adminpanel.twig', array('UserEmail' => $adminLogInEmail));
    }

    public function showAdminSettings() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '1'));
        return $this->app['twig']->render('admin/admin_settings.twig', array('userData' => $user, 'pageHeading' => 'Admin'));
    }

    public function showUserSettings() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '0'));
        return $this->app['twig']->render('admin/user_settings.twig', array('userData' => $user, 'pageHeading' => 'User'));
    }

    public function addAllUsers($userType) {
        return $this->app['twig']->render('admin/addallusers.twig', array('formHeading' => 'Add ', 'userType' => $userType));
    }

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
            $returnStatement = FALSE;
        }

        $uploadedFile->move('uploads/', $fileName . '.' . $uploadedFile->guessExtension());
        $returnStatement = TRUE;

        return $returnStatement;
    }

    public function doAddUser(Request $request, $userType) {

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $postedFormData = $request->request->all();
        $prevPassword = $postedFormData['userPassword'];
        $newPassword = $postedFormData['password'];

        if ($postedFormData['userName'] == '' || $postedFormData['userEmail'] == '' || $postedFormData['password'] == '' || $postedFormData['isAdmin'] == '') {
            $sessionData->getFlashBag()->add("alert_danger", "No field should left blank");
            return $this->app->redirect("/adduser/" . $userType);
        }

        if ($postedFormData['userId'] == '') {

            $user = new User();
            $sessionData->getFlashBag()->add("alert_success", $userType . " added successfully!");
        } else {

            $user = $entityManager->find('Entity\User', $postedFormData['userId']);
            $sessionData->getFlashBag()->add('alert_info', $userType . ' details edited successfully');
        }

        try {
            $user->setUserName($postedFormData['userName']);
            $user->setEmail($postedFormData['userEmail']);

            $user->setIs_Admin($postedFormData['isAdmin']);
            $user->setLocation($postedFormData['location']);
            $user->setUser_Address($postedFormData['user_address']);

            if ($prevPassword != $newPassword) {
                $user->setPassword(md5($postedFormData['password']));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $uploadedFile = $request->files->get('uploadedFile');

            if ($userType != 'Admin' && !empty($uploadedFile)) {

                $generatedUserData = $entityManager->getRepository('Entity\User')->findBy(array('email' => $postedFormData['userEmail']));
                $generatedId = $generatedUserData[0]->getId();

                if ($this->newFileUpload($uploadedFile, $generatedId)) {
                    $sessionData->getFlashBag()->add("alert_success", "File added successfully");
                }
            }

            if ($userType == 'User') {
                $reDirectUrl = "/usersetting";
            } else {
                $reDirectUrl = "/adminsetting";
            }

            return $this->app->redirect($reDirectUrl);
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add("alert_danger", "Email id is already registered, unique required!");

            $userData = ['is_admin' => $postedFormData['isAdmin'],
                'username' => $postedFormData['userName'],
                'email' => $postedFormData['userEmail'],
                'location' => $postedFormData['location'],
                'user_address' => $postedFormData['user_address'],
                'password' => $postedFormData['password']
            ];
            return $this->app['twig']->render('admin/addallusers.twig', array(
                        'userData' => $userData,
                        'formHeading' => 'Edit ',
                        'userType' => $userType));
        }
    }

    public function editUserData($userType, $id) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\User');
        $categories = $categoryRepository->findAll();

        $userDetails = $entityManager->find('Entity\User', $id);

        return $this->app['twig']->render('admin/addallusers.twig', array(
                    'userData' => $userDetails,
                    'formHeading' => 'Edit ',
                    'userType' => $userType));
    }

    public function deleteUserData($userType, $id) {
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $userDetails = $entityManager->find('Entity\User', $id);
        $entityManager->remove($userDetails);

        $entityManager->flush();

        //Delete previous file of this user
        $fs = new FileSystem();
        if ($fs->exists('uploads/' . $id . '.docx')) {
            $fileFullName = $id . '.docx';
        } elseif ($fs->exists('uploads/' . $id . '.doc')) {
            $fileFullName = $id . '.doc';
        } else {
            $fileFullName = $id . '.pdf';
        }

        if ($fs->exists('uploads/' . $fileFullName)) {
            unlink('uploads/' . $fileFullName);
        }
        //delete block ends
        
        $sessionData->getFlashBag()->add('alert_success', $userType . ' deleted from database');

        if ($userType == 'User') {
            $redirectUrl = '/usersetting';
        } else {
            $redirectUrl = '/adminsetting';
        }

        return $this->app->redirect($redirectUrl);
    }

//    public function generateQuestions($categoryId,$limit) {
//        $resultSetMapping = new ResultSetMapping();
//        $entityManager = $this->app['doctrine'];
//        $query = $entityManager->createNativeQuery('select * from questions where categoryId = ?  order by rand limit ?', $resultSetMapping);
//        $query->setParameter(1,$categoryId);
//        $query->setParameter(2,$limit);
//        $result = $query->getResult();
//        return $result;
//    }

    public function downloadFile($filename) {
        $sessionData = $this->app['session'];
        $file = 'uploads/' . $filename;

        // check if file exists
        $fs = new FileSystem();
        if ($fs->exists($file . '.docx')) {
            $filePath = $file . '.docx';
        } elseif ($fs->exists($file . '.doc')) {
            $filePath = $file . '.doc';
        } else {
            $filePath = $file . '.pdf';
        }

        if (!$fs->exists($filePath)) {
            $sessionData->getFlashBag()->add('alert_danger', 'File not found!');
            return $this->app->redirect('/usersetting');
        }
        // prepare BinaryFileResponse
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE, $filename, iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }

    public function adminLogout() {
        $sessionData = $this->app['session'];
        $sessionData->clear();
        return $this->app->redirect("/admin");
    }

    public function checkAdminSession() {
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $validAdminSession = $sessionData->get('loginAdminSession');

        if (!empty($validAdminSession)) {
            return true;
        }

        return false;
    }

    public function checkUserRegistration($email) {
        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('email' => $email, 'is_admin' => '0'));

        if (!empty($user)) {
            echo '1';
            exit;
        } else {
            echo '0';
            exit;
        }
    }

}
