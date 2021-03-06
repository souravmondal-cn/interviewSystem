<?php

namespace Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Service\FileHandler;
use Entity\User;
use Controller\Controller;

class AdminController extends Controller {

    public function getAdminLoginForm() {
        
        return $this->app['twig']->render('login.twig', array('formName' => 'Admin'));
    }

    public function adminPanel() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect(BASEPATH."/admin/login");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app['twig']->render('admin/adminpanel.twig', array('UserEmail' => $adminLogInEmail, 'pageTitle' => 'Admin Panel'));
    }

    public function showAdminSettings() {
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('isAdmin' => '1'));
        return $this->app['twig']->render(
            'admin/admin_settings.twig',
            array(
                'userData' => $user,
                'pageHeading' => 'Admin',
                'pageTitle' => 'Admin Settings'
            )
        );
    }

    public function showUserSettings() {
        
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('isAdmin' => '0'));
        return $this->app['twig']->render('admin/user_settings.twig', array('userData' => $user, 'pageHeading' => 'User', 'pageTitle' => 'User Settings'));
    }

    public function getAddUserForm($userType) {
        
        return $this->app['twig']->render('admin/addallusers.twig', array('formHeading' => 'Add ', 'userType' => $userType, 'pageTitle' => 'Add '.$userType));
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
                $postedFormData['password'] == ''
        ) {
            $sessionData->getFlashBag()->add("alert_danger", "No field should left blank");
            return $this->app->redirect(BASEPATH."/adduser/" . $userType);
        }

        if ($postedFormData['userId'] == '') {

            $user = new User();
            $formHeading = 'Add ';
            $redirectUrl = BASEPATH."/adduser/" . $userType;
            $sessionData->getFlashBag()->add("alert_success", $userType . " added successfully!");
        } else {

            $user = $entityManager->find('Entity\User', $postedFormData['userId']);
            $formHeading = 'Edit ';
            $redirectUrl = BASEPATH."/usersetting";
            $sessionData->getFlashBag()->add('alert_info', $userType . ' details edited successfully');
        }


        $user->setUserName($postedFormData['userName']);
        $user->setUserEmail($postedFormData['userEmail']);

        $user->setIsAdmin($postedFormData['isAdmin']);
        $user->setOfficeLocation($postedFormData['officeLocation']);
        $user->setUserAddress($postedFormData['userAddress']);
        if(isset($postedFormData['allowAccess'])) {
            $user->setAllowAccess($postedFormData['allowAccess']);
        } else {
            $user->setAllowAccess("0");
        }
        

        if ($prevPassword != $newPassword) {
            $user->setPassword($postedFormData['password']);
        }
        
        try {
            $entityManager->persist($user);
            $entityManager->flush();

            if (null !== $request->files->get('resumeFile')) {
                $fs = new FileHandler();
                $fs->fileUpload($request->files->get('resumeFile'), $user->getId(), UPLOAD_PATH);
            }

            return $this->app->redirect($redirectUrl);
        } catch (UniqueConstraintViolationException $ex) {

            $sessionData->getFlashBag()->add("alert_danger", "Email id is already registered, unique required!");

            $userData = ['isAdmin' => $postedFormData['isAdmin'],
                'userName' => $postedFormData['userName'],
                'userEmail' => $postedFormData['userEmail'],
                'officeLocation' => $postedFormData['officeLocation'],
                'userAddress' => $postedFormData['userAddress'],
                'password' => $postedFormData['password'],
                'allowAccess' => $postedFormData['allowAccess']
            ];
            return $this->app['twig']->render('admin/addallusers.twig', array(
                        'userData' => $userData,
                        'formHeading' => $formHeading,
                        'userType' => $userType,
                        'pageTitle' => 'Add '.$userType));
        }
    }

    public function editUserData($userType, $id) {
        
        if ($this->checkAdminSession() === false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];

        $userDetails = $entityManager->find('Entity\User', $id);
        return $this->app['twig']->render('admin/addallusers.twig', array(
                    'userData' => $userDetails,
                    'formHeading' => 'Edit ',
                    'userType' => $userType,
                    'pageTitle' => 'Edit '.$userType));
    }

    public function deleteUserData($userType, $id) {
        
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $userDetails = $entityManager->find('Entity\User', $id);
        $entityManager->remove($userDetails);
        $entityManager->flush();

        $fileHandler = new FileHandler();
        $fileHandler->deleteExistingFile($id, UPLOAD_PATH);
        
        $sessionData->getFlashBag()->add('alert_success', $userType . ' deleted from database');
        $redirectUrl = "/" . strtolower($userType) . "setting";

        return $this->app->redirect(BASEPATH.$redirectUrl);
    }

    public function downloadFile($filename) {
        
        $fileHandler = new FileHandler();
        
        $fileResponse = $fileHandler->downloadExistingFile($filename, UPLOAD_PATH);
        if (!$fileResponse) {
            
            return $this->app->redirect(BASEPATH.'/usersetting');
        } else {
            
            return $fileResponse;
        }
        
    }

    public function adminLogout() {
        $sessionData = $this->app['session'];
        $sessionData->clear();
        return $this->app->redirect(BASEPATH."/admin");
    }

}
