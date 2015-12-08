<?php

namespace Controller\Admin;

class Controller {

    /**
     * @var \Silex\Application 
     */
    protected $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function checkAdminSession() {
        $sessionData = $this->app['session'];

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
