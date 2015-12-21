<?php

namespace Controller\FrontEnd;

use Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
            return $this->app->redirect("/login");
        }

        $sessionUserData->set('loggedInId', $loginInfo->getId());
        return $this->app->redirect('/');
    }

    public function home() {

        $sessionData = $this->app['session'];
        $loggerId = $sessionData->get('loggedInId');

        if (empty($loggerId)) {

            $route = '/login';
        } else {

            $route = $this->checkLoggerId($loggerId);
        }

        return $this->app->redirect($route);
    }

    public function checkLoggerId($loggerId) {

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $userDetails = $entityManager->getRepository('Entity\User')->find($loggerId);

        if (empty($userDetails)) {

            $returnRoute = '/login';
        } else {

            $isAdmin = $userDetails->getIsAdmin();

            if (!empty($isAdmin) && $isAdmin == 1) {

                $sessionData->set('adminSession', true);
                $sessionData->getFlashBag()->add('alert_success', 'Welcome to admin panel');
                $returnRoute = '/admin';
            } else {

                $sessionData->set('userSession', true);
                $returnRoute = '/dashboard';
            }
        }

        return $returnRoute;
    }

}
