<?php

namespace Controller;

use Entity\User;
use Controller\FrontEnd\ExamController;
use Controller\Controller;
use Service\FileHandler;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use DateTime;

class UserController extends Controller {

    public function getLoginFrom() {
        return $this->app['twig']->render('login.twig', array('formName' => 'User'));
    }

    public function getRegistrationForm() {
        return $this->app['twig']->render('registration.twig');
    }

    public function registerUser(Request $request) {

        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $user = new User();
        $user->setUserName($request->request->get('userName'));
        $user->setUserEmail($request->request->get('userEmail'));
        $user->setPassword($request->request->get('userPassword'));
        $user->setOfficeLocation($request->request->get('officeLocation'));
        $user->setUserAddress($request->request->get('userAddress'));

        try {

            $entityManager->persist($user);
            $entityManager->flush();

            if (null !== $request->files->get('resumeFile')) {
                $fs = new FileHandler();
                $fs->fileUpload($request->files->get('resumeFile'), $user->getId(), UPLOAD_PATH);
            }

            $sessionUserData->getFlashBag()->add('alert_success', 'Registration successful');
            return $this->app->redirect("/login");
        } catch (UniqueConstraintViolationException $ex) {

            $sessionUserData->getFlashBag()->add('alert_danger', 'Sorry, this email id is already registered!');
            return $this->app->redirect("/register");
        }
    }

    public function dashboard() {

        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        if ($this->checkUserSession() === false) {
            return $this->app->redirect("/login");
        }

        $loggedInId = $sessionUserData->get('loggedInId');

        $userDetails = $entityManager->getRepository('Entity\User')->find($loggedInId);

        $logInEmail = $userDetails->getUserEmail();
        $loggerName = $userDetails->getUserName();

        $exam = new ExamController($this->app);
        $examData = $exam->getExamData($loggedInId);
        $successData = $exam->getExamSuccessStatus($loggedInId);

        if (!empty($examData)) {
            $examset = 'true';
        } else {
            $examset = 'false';
        }

        return $this->app['twig']->render('dashboard.twig', [
                    'UserId' => $loggedInId,
                    'UserEmail' => $logInEmail,
                    'UserName' => $loggerName,
                    'examset' => $examset,
                    'success' => $successData
        ]);
    }

    public function logout() {

        $sessionUserData = $this->app['session'];
        $sessionUserData->remove('loginSession');
        $sessionUserData->clear();
        return $this->app->redirect("/");
    }

}
