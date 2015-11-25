<?php

namespace Controller;

use Entity\User;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \DateTime;
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
        $entityManager = $this->app['doctrine'];
        
        $validSession = $sessionData->get('loginSession');
        if(empty($validSession)) {
            return $this->app->redirect("/");
        }
        
        $questionRepository = $entityManager->getRepository('Entity\Questions');
        $examdata = $this->getExamdata($email);
        
        if(empty($examdata)) {
            $sessionData->getFlashBag()->add('user_message','Examination finished');
            return $this->app->redirect("/dashboard");
        }
        $getQuestions = $examdata[0]->getQuestions();
        $examId = $examdata[0]->getExamId();
        $questions = explode(',', $getQuestions);
        $totalQuestions = count($questions);
        $questionData = $questionRepository->findBy(['qid' => $questions]);
        
        $sessionData->set('questionData',$questionData);
        $sessionData->set('totalQuestions',$totalQuestions);
        $sessionData->set('flag',0);
        $sessionData->set('examId',$examId);
        return $this->app->redirect('/displayQuestion');
    }
    
    public function displayQuestion(){
        $entityManager = $this->app['doctrine'];
        $questionRepository = $entityManager->getRepository('Entity\Questions');
        $sessionData = $this->app['session'];
        
        $validSession = $sessionData->get('loginSession');
        $logInEmail = $sessionData->get('loginEmail');
        $loggerName = $sessionData->get('loggerName');
        $totalQuestions = $sessionData->get('totalQuestions');
        $questionData = $sessionData->get('questionData');
        //$sessionData->set('counter',$totalQuestions);
        $flag = $sessionData->get('flag');
        
        if($flag == $totalQuestions) {
            $sessionData->remove('questionData');
            $sessionData->remove('totalQuestions');
            $sessionData->remove('flag');
            $sessionData->remove('examId');
            $sessionData->remove('examId');
            
            $sessionData->getFlashBag()->add('user_message','Examination finished');
            return $this->app->redirect('/dashboard');
        }
        
        $displayQuestion = $questionData[$flag];
        
        return $this->app['twig']->render('examnow.twig',[
            'UserEmail' => $logInEmail,
            'UserName' => $loggerName,
            'displayQuestion' => $displayQuestion
                ]);
    }
    
    public function examSubmit(Request $request) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $examId = $sessionData->get('examId');
        $totalQuestions = $sessionData->get('totalQuestions');
        $questionData = $sessionData->get('questionData');
        $flag = $sessionData->get('flag');
        
        $postedExamData = $request->request->all();
        $qid = $postedExamData['qid'];
        
        if(empty($postedExamData['answer'])) {
            $answer = '';
        }
        else {
        $answer = $postedExamData['answer'];
        }
        
        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);
        
        $questionDetail = $entityManager->find('Entity\Questions', $qid);
        $originalAnswer = $questionDetail->getAnswer();
        
        if($originalAnswer == $answer) {
            $scorePoint = 1; 
        }
        else{
            $scorePoint = 0;
        }
        
        $prevAnswers = $examDetail->getCorrect_Answers();
        $newAnswers = $prevAnswers+$scorePoint;
        $examDetail->setCorrect_Answers($newAnswers);
        
        unset($questionData[$flag]);
        $sessionData->set('questionData', $questionData);
            
        $flagNew = $flag+1;
        $sessionData->set('flag',$flagNew);
        
        date_default_timezone_set("Asia/Calcutta");
        $examDetail->setDate_Completed(new DateTime());
        $entityManager->persist($examDetail);
        $entityManager->flush();
        
        return $this->app->redirect('/displayQuestion');
    }
    
    public function logout() {
       $sessionData = $this->app['session'];
       $sessionData->clear();
       return $this->app->redirect("/");
    }
}
