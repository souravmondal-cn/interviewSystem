<?php

namespace Controller;

use Entity\User;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class HomeController {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function login() {
        return $this->app['twig']->render('login.twig');
    }

    public function showRegistrationForm() {
        return $this->app['twig']->render('registration.twig');
    }
    
    public function registerNewUser(Request $request) {
        
        $sessionData = $this->app['session'];
        $postedUserData = $request->request->all();
        
        try {
            $user = new User();
            $user->setUserName($postedUserData['userName']);
            $user->setEmail($postedUserData['userEmail']);
            $user->setPassword($postedUserData['userPassword']);

            $entityManager = $this->app['doctrine'];
            $entityManager->persist($user);
            $entityManager->flush();

            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('message','Registration successful');
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add('message','Sorry, this email id is already registered!');
            return $this->app->redirect("/register");
        }

        return $this->app->redirect("/");
    }
    
    public function doLogin(Request $request) {
        
        $postedLogInData = $request->request->all();
        $sessionData = $this->app['session'];
        
        $loginDetails = array(
            'email' => $postedLogInData['loginEmail'],
            'password' => $postedLogInData['loginPassword']
        );
        
        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\User')->findBy($loginDetails);
        
        if(empty($loginInfo)) {
            $sessionData->getFlashBag()->add('message','Sorry, user email and password does not match!');
            return $this->app->redirect("/");
        }
        
        $sessionData = $this->app['session'];
        $sessionData->set('loginSession',true);
        $sessionData->set('loginEmail',$loginDetails['email']);
        $sessionData->set('loggerName',$loginInfo[0]->getUserName());
        return $this->app->redirect('/dashboard');
    }
    
    public function dashboard() {
        
        $sessionData = $this->app['session'];
        $validSession = $sessionData->get('loginSession');
        
        if(empty($validSession)) {
            return $this->app->redirect("/");
        }
        
        $logInEmail = $sessionData->get('loginEmail');
        $loggerName = $sessionData->get('loggerName');
        return $this->app['twig']->render('dashboard.twig',array('UserEmail' => $logInEmail, 'UserName' => $loggerName));
    }
    
    public function logout() {
       $sessionData = $this->app['session'];
       $sessionData->clear();
       return $this->app->redirect("/");
    }
}
