<?php

namespace Controller;

use Controller\Common\Common;
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

        $sessionUserData = $this->app['session'];
        $postedUserData = $request->request->all();

        try {
            $user = new User();
            $user->setUserName($postedUserData['userName']);
            $user->setEmail($postedUserData['userEmail']);
            $user->setPassword(md5($postedUserData['userPassword']));
            $user->setIs_Admin('0');
            $user->setLocation($postedUserData['location']);
            $user->setUser_Address($postedUserData['address']);

            $entityManager = $this->app['doctrine'];
            $entityManager->persist($user);
            $entityManager->flush();

            $sessionUserData->getFlashBag()->add('alert_success', 'Registration successful');

            $uploadedFile = $request->files->get('uploadedFile');

            if (!empty($uploadedFile)) {

                $generatedUserData = $entityManager->getRepository('Entity\User')->findBy(array('email' => $postedUserData['userEmail']));
                $generatedId = $generatedUserData[0]->getId();

                /* @var $common Controller\Common\Common */
                $common = new Common;
                if ($common->newFileUpload($uploadedFile, $generatedId)) {
                    $sessionUserData->getFlashBag()->add("alert_success", "File added successfully");
                }
            }
        } catch (UniqueConstraintViolationException $ex) {
            $sessionUserData->getFlashBag()->add('alert_danger', 'Sorry, this email id is already registered!');
            return $this->app->redirect("/register");
        }

        return $this->app->redirect("/");
    }

    public function doLogin(Request $request) {

        $postedLogInData = $request->request->all();
        $sessionUserData = $this->app['session'];

        $loginDetails = array(
            'email' => $postedLogInData['loginEmail'],
            'password' => md5($postedLogInData['loginPassword']),
            'is_admin' => '0'
        );

        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\User')->findBy($loginDetails);

        if (empty($loginInfo)) {
            $sessionUserData->getFlashBag()->add('alert_danger', 'Sorry, email id and password does not matched!');
            return $this->app->redirect("/");
        }

        $sessionUserData->set('loginSession', true);
        $sessionUserData->set('loginEmail', $loginDetails['email']);
        $sessionUserData->set('loggerName', $loginInfo[0]->getUserName());

        return $this->app->redirect('/dashboard');
    }

    public function dashboard() {
        $sessionUserData = $this->app['session'];

        $validSession = $sessionUserData->get('loginSession');
        $logInEmail = $sessionUserData->get('loginEmail');
        $loggerName = $sessionUserData->get('loggerName');

        if (empty($validSession)) {
            return $this->app->redirect("/");
        }

        $examData = $this->getExamdata($logInEmail);
        $successData = $this->getExamSuccess($logInEmail);

        if (!empty($examData)) {
            $examset = 'true';
        } else {
            $examset = 'false';
        }

        return $this->app['twig']->render('dashboard.twig', ['UserEmail' => $logInEmail,
                    'UserName' => $loggerName,
                    'examset' => $examset,
                    'success' => $successData
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

    public function getExamSuccess($emailId) {
        $entityManager = $this->app['doctrine'];
        $successData = $entityManager->getRepository('Entity\Examination')->findOneBy(
                ['email' => $emailId], ['examid' => 'desc']
        );

        $isQualified = $successData->getIs_Qualified();

        if (!empty($isQualified)) {
            $success = TRUE;
        } else {
            $success = FALSE;
        }

        return $success;
    }

    public function examNow($email) {
        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $validSession = $sessionUserData->get('loginSession');
        if (empty($validSession)) {
            return $this->app->redirect("/");
        }

        $questionRepository = $entityManager->getRepository('Entity\Questions');
        $examdata = $this->getExamdata($email);

        if (empty($examdata)) {
            return $this->app->redirect('/dashboard');

            $sessionUserData->getFlashBag()->add('user_message', 'Examination finished');
            return $this->app->redirect("/dashboard");
        }
        $getQuestions = $examdata[0]->getQuestions();
        $examId = $examdata[0]->getExamId();
        $totaltime = $examdata[0]->getTotalTime();
        $questions = explode(',', $getQuestions);
        $totalQuestions = count($questions);
        $questionData = $questionRepository->findBy(['qid' => $questions]);

        $sessionUserData->set('questionData', $questionData);
        $sessionUserData->set('totalQuestions', $totalQuestions);
        $sessionUserData->set('flag', 0);
        $sessionUserData->set('examId', $examId);
        $sessionUserData->set('validExam', TRUE);
        $sessionUserData->set('totaltime', $totaltime);
        return $this->app->redirect('/displayQuestion');
    }

    public function displayQuestion() {
        $sessionUserData = $this->app['session'];
        $totaltime = $sessionUserData->get('totaltime');
        header("Refresh: $totaltime; url=/examsubmit");

        $validExam = $sessionUserData->get('validExam');
        if ($validExam == FALSE) {
            return $this->app->redirect('/examsubmit');
        }

        $sessionUserData->set('validExam', FALSE);

        $entityManager = $this->app['doctrine'];
        $questionRepository = $entityManager->getRepository('Entity\Questions');


        $validSession = $sessionUserData->get('loginSession');
        $logInEmail = $sessionUserData->get('loginEmail');
        $loggerName = $sessionUserData->get('loggerName');
        $totalQuestions = $sessionUserData->get('totalQuestions');
        $questionData = $sessionUserData->get('questionData');

        $flag = $sessionUserData->get('flag');

        if ($flag == $totalQuestions) {
            $qa = $sessionUserData->get('examQa');

            $sessionUserData->remove('questionData');
            $sessionUserData->remove('totalQuestions');
            $sessionUserData->remove('flag');
            $sessionUserData->remove('examId');
            $sessionUserData->remove('examId');

            $sessionUserData->getFlashBag()->add('user_message', 'Examination finished');
            return $this->app->redirect('/dashboard');
        }

        $displayQuestion = $questionData[$flag];

        return $this->app['twig']->render('examnow.twig', [
                    'UserEmail' => $logInEmail,
                    'UserName' => $loggerName,
                    'displayQuestion' => $displayQuestion
        ]);
    }

    public function examSubmit(Request $request) {
        $entityManager = $this->app['doctrine'];
        $sessionUserData = $this->app['session'];
        $examId = $sessionUserData->get('examId');

        $questionData = $sessionUserData->get('questionData');
        $flag = $sessionUserData->get('flag');

        $postedExamData = $request->request->all();

        $qid = $postedExamData['qid'];

        if (empty($postedExamData['answer'])) {
            $answer = '';
        } else {
            $answer = $postedExamData['answer'];
        }

        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $questionDetail = $entityManager->find('Entity\Questions', $qid);

        /* code block : exam question and respective answer submitted by user */
        $qa[$qid] = $answer;

        $qaa = $examDetail->getSubmits();
        $json = json_encode($qa);
        if ($qaa === '' || $qaa === NULL) {
            $qaa = $json;
        } else {
            $qaa = rtrim($qaa, '}') . ',' . ltrim($json, '{');
        }

        $examDetail->setSubmits($qaa);
        /* End code block : exam question and respective answer submitted by user */

        $originalAnswer = $questionDetail->getAnswer();

        if ($originalAnswer == $answer) {
            $scorePoint = 1;
        } else {
            $scorePoint = 0;
        }

        $prevAnswers = $examDetail->getCorrect_Answers();
        $newAnswers = $prevAnswers + $scorePoint;
        $examDetail->setCorrect_Answers($newAnswers);

        unset($questionData[$flag]);
        $sessionUserData->set('questionData', $questionData);

        $flagNew = $flag + 1;
        $sessionUserData->set('flag', $flagNew);

        date_default_timezone_set("Asia/Calcutta");
        $examDetail->setDate_Completed(new DateTime());
        $entityManager->persist($examDetail);
        $entityManager->flush();

        $sessionUserData->set('validExam', TRUE);
        return $this->app->redirect('/displayQuestion');
    }

    public function forceSubmit() {
        $entityManager = $this->app['doctrine'];
        $sessionUserData = $this->app['session'];
        $examId = $sessionUserData->get('examId');

        if (empty($examId)) {
            return $this->app->redirect('/dashboard');
        }
        $sessionUserData->remove('examId');
        $sessionUserData->remove('totalQuestions');
        $sessionUserData->remove('questionData');
        $sessionUserData->remove('flag');

        $examDetail = $entityManager->find('Entity\Examination', $examId);
        date_default_timezone_set("Asia/Calcutta");
        $examDetail->setDate_Completed(new DateTime());
        $entityManager->persist($examDetail);
        $entityManager->flush();
        $sessionUserData->getFlashBag()->add('user_message', 'Examination finished');
        return $this->app->redirect('/dashboard');
    }

    public function logout() {
        $sessionUserData = $this->app['session'];
        $sessionUserData->remove('loginSession');
        $sessionUserData->clear();
        return $this->app->redirect("/");
    }

}
