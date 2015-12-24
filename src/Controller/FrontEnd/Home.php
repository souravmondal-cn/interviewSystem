<?php

namespace Controller\FrontEnd;

use Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Controller\FrontEnd\ExamController;

class Home extends Controller {

    public function doLogin(Request $request) {

        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $loginInfo = $entityManager->getRepository('Entity\User')->findOneBy(array(
            'userEmail' => $request->request->get('loginEmail'),
            'password' => md5($request->request->get('loginPassword'))
        ));

        if (empty($loginInfo)) {

            $sessionUserData->getFlashBag()->add('alert_danger', 'Sorry, email and password does not match');
            return $this->app->redirect(BASEPATH . "/login");
        }

        $sessionUserData->set('loggedInId', $loginInfo->getId());
        return $this->app->redirect(BASEPATH . '/');
    }

    public function home() {

        $sessionData = $this->app['session'];
        $loggerId = $sessionData->get('loggedInId');

        if (empty($loggerId)) {
            $route = BASEPATH . '/login';
        } else {
            $route = $this->getProperRouteForUser($loggerId);
        }

        return $this->app->redirect($route);
    }

    public function getProperRouteForUser($loggerId) {

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];
        $defaultRoute = BASEPATH . '/login';

        $userDetails = $entityManager->getRepository('Entity\User')->find($loggerId);

        if (empty($userDetails)) {
            return $defaultRoute;
        }

        $isAdmin = $userDetails->getIsAdmin();
        if (!empty($isAdmin) && $isAdmin == 1) {

            $sessionData->set('adminSession', true);
            $sessionData->getFlashBag()->add('alert_success', 'Welcome to admin panel');
            $defaultRoute = BASEPATH . '/admin';

            return $defaultRoute;
        }

        if ($this->checkAllowedCandidate($loggerId)) {

            $sessionData->set('userSession', true);
            $defaultRoute = BASEPATH . '/dashboard';

            return $defaultRoute;
        }

        return $defaultRoute;
    }

    public function checkAllowedCandidate($loggerId) {

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $userRepository = $entityManager->getRepository('Entity\User');
        $allowedUserDetails = $userRepository->findOneby(array(
            'id' => $loggerId,
            'isAdmin' => false,
            'allowAccess' => true
        ));

        $exam = new ExamController($this->app);
        $examData = $exam->getExamData($loggerId);
        $successData = $exam->getExamSuccessStatus($loggerId);
        
        if (!empty($allowedUserDetails) && !empty($examData)) {
            $sessionData->set('userSession', true);
            return true;
        }

        $sessionData->getFlashBag()->add('alert_danger', 'Sorry! Access denied!');
        return false;
    }

}
