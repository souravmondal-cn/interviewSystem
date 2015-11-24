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
            $user->setPassword(md5($postedUserData['userPassword']));
            $user->setIs_Admin('0');
            
            $entityManager = $this->app['doctrine'];
            $entityManager->persist($user);
            $entityManager->flush();

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
            'password' => md5($postedLogInData['loginPassword']),
            'is_admin' => '0'
        );
        
        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\User')->findBy($loginDetails);
        
        if(empty($loginInfo)) {
            $sessionData->getFlashBag()->add('message','Sorry, user email and password does not match!');
            return $this->app->redirect("/");
        }
        
        $sessionData->set('loginSession',true);
        $sessionData->set('loginEmail',$loginDetails['email']);
        $sessionData->set('loggerName',$loginInfo[0]->getUserName());
        
        return $this->app->redirect('/dashboard');
    }
    
    public function dashboard() {
        $sessionData = $this->app['session'];
        
        $validSession = $sessionData->get('loginSession');
        $logInEmail = $sessionData->get('loginEmail');
        $loggerName = $sessionData->get('loggerName');
        
        if(empty($validSession)) {
            return $this->app->redirect("/");
        }
        
        $examData = $this->getExamdata($logInEmail);
        
        if(!empty($examData)) {
            $examset = 'true';
        }
        else {
            $examset = 'false';
        }
        
        return $this->app['twig']->render('dashboard.twig',  ['UserEmail' => $logInEmail, 
                'UserName' => $loggerName,
                'examset' => $examset
                ]);
    }
    
    public function getExamdata($emailId) {
        $entityManager = $this->app['doctrine'];
        $examData = $entityManager->getRepository('Entity\Examination')->findBy(array(
                'email' => $emailId,
                'date_completed' => null)
            );
        return $examData;
    }
    
    public function examNow($email) {
        $sessionData = $this->app['session'];
        $entitymanager = $this->app['doctrine'];
        
        $validSession = $sessionData->get('loginSession');
        $logInEmail = $sessionData->get('loginEmail');
        $loggerName = $sessionData->get('loggerName');
        
        $questionRepository = $entitymanager->getRepository('Entity\Questions');
        
        $examdata = $this->getExamdata($email);
        $getQuestions = $examdata[0]->getQuestions();
        $questions = explode(',', $getQuestions);
        
        $questionData = $questionRepository->findBy(['qid' => $questions]);

        return $this->app['twig']->render('examnow.twig',[
            'UserEmail' => $logInEmail,
            'UserName' => $loggerName,
            'questions' => $questionData
                ]);
    }
    
    public function logout() {
       $sessionData = $this->app['session'];
       $sessionData->clear();
       return $this->app->redirect("/");
    }
}
