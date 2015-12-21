<?php

namespace Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Service\FileHandler;
use Entity\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Controller\Controller;
use service\CheckAdmin;
use Symfony\Component\Filesystem\Filesystem;

class AdminController extends Controller {

    public function getAdminLoginForm() {
        return $this->app['twig']->render('login.twig', array('formName' => 'Admin'));
    }

    public function adminPanel() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect("/admin/login");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $sessionData->getFlashBag()->add('alert_success', 'Welcome to admin panel');
        return $this->app['twig']->render('admin/adminpanel.twig', array('UserEmail' => $adminLogInEmail));
    }

    public function showAdminSettings() {
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('isAdmin' => '1'));
        return $this->app['twig']->render('admin/admin_settings.twig', array('userData' => $user, 'pageHeading' => 'Admin'));
    }

    public function showUserSettings() {
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('isAdmin' => '0'));
        return $this->app['twig']->render('admin/user_settings.twig', array('userData' => $user, 'pageHeading' => 'User'));
    }

    public function addAllUsers($userType) {
        return $this->app['twig']->render('admin/addallusers.twig', array('formHeading' => 'Add ', 'userType' => $userType));
    }

    public function doAddUser(Request $request, $userType) {

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $postedFormData = $request->request->all();
        $prevPassword = $postedFormData['userPassword'];
        $newPassword = $postedFormData['password'];

        if (
                $postedFormData['userName'] == '' ||
                $postedFormData['userEmail'] == '' ||
                $postedFormData['password'] == '' ||
                $postedFormData['isAdmin'] == ''
        ) {
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
            $user->setUserEmail($postedFormData['userEmail']);

            $user->setIsAdmin($postedFormData['isAdmin']);
            $user->setOfficeLocation($postedFormData['officeLocation']);
            $user->setUserAddress($postedFormData['userAddress']);

            if ($prevPassword != $newPassword) {
                $user->setPassword($postedFormData['password']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if (null !== $request->files->get('resumeFile')) {
                $fs = new FileHandler();
                $fs->fileUpload($request->files->get('resumeFile'), $user->getId(), UPLOAD_PATH);
            }

            $reDirectUrl = "/".strtolower($userType)."setting";

            return $this->app->redirect($reDirectUrl);
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add("alert_danger", "Email id is already registered, unique required!");

            $userData = ['isAdmin' => $postedFormData['isAdmin'],
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
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];

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

        $fs = new FileSystem();

        if ($fs->exists(UPLOAD_PATH . $id . '.docx')) {
            $fullPath = UPLOAD_PATH . $id . '.docx';
        } elseif ($fs->exists(UPLOAD_PATH . $id . '.doc')) {
            $fullPath = UPLOAD_PATH . $id . '.doc';
        } else {
            $fullPath = UPLOAD_PATH . $id . '.pdf';
        }
        unlink($fullPath);

        $sessionData->getFlashBag()->add('alert_success', $userType . ' deleted from database');

        if ($userType === 'User') {
            $redirectUrl = '/usersetting';
        } else {
            $redirectUrl = '/adminsetting';
        }

        return $this->app->redirect($redirectUrl);
    }

    public function downloadFile($filename) {
        $sessionData = $this->app['session'];
        $fs = new FileSystem();

        $file = UPLOAD_PATH . $filename;

        if ($fs->exists($file . '.docx')) {
            $fullPath = $file . '.docx';
        } elseif ($fs->exists($file . '.doc')) {
            $fullPath = $file . '.doc';
        } else {
            $fullPath = $file . '.pdf';
        }

        if (!$fs->exists($fullPath)) {
            $sessionData->getFlashBag()->add('alert_danger', 'File not found!');
            return $this->app->redirect('/usersetting');
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

    public function adminLogout() {
        $sessionData = $this->app['session'];
        $sessionData->clear();
        return $this->app->redirect("/admin");
    }

}
