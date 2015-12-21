<?php

namespace Controller;

class Controller {

    /**
     * @var \Silex\Application 
     */
    protected $app;
    public $baseUrl;

    public function __construct($app) {
        $this->app = $app;
    }

    public function checkAdminSession() {
        $sessionData = $this->app['session'];

        $validAdminSession = $sessionData->get('adminSession');
        if (!empty($validAdminSession)) {
            return true;
        }

        return false;
    }

    public function checkUserSession() {
        $sessionData = $this->app['session'];

        $validUserSession = $sessionData->get('userSession');
        if (!empty($validUserSession)) {
            
            return true;
        }

        return false;
    }

    public function checkUserRegistration($email) {
        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('userEmail' => $email, 'isAdmin' => '0'));

        if (!empty($user)) {
            
            return true;
        } else {
            
            return false;
        }
    }

}
