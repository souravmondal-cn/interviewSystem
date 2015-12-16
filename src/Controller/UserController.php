<?php

namespace Controller;

use Entity\User;
use Controller\Controller;
use Service\FileHandler;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use DateTime;

class UserController extends Controller {

    public function getLoginFrom() {
        return $this->app['twig']->render('login.twig');
    }

    public function getRegistrationForm() {
        return $this->app['twig']->render('registration.twig');
    }

    public function registerUser(Request $request) {

        $sessionUserData = $this->app['session'];
        $user = new User();
        $user->setUserName($request->request->get('userName'));
        $user->setUserEmail($request->request->get('userEmail'));
        $user->setPassword($request->request->get('userPassword'));
        $user->setOfficeLocation($request->request->get('location'));
        $user->setUserAddress($request->request->get('address'));

        $entityManager = $this->app['doctrine'];
        try {
            $entityManager->persist($user);
            $entityManager->flush();
            $sessionUserData->getFlashBag()->add('alert_success', 'Registration successful');
            if (!empty($request->files->get('resumeFile'))) {
                $fs = new FileHandler();
                if ($fs->fileUpload($request->files->get('resumeFile'), $user->getId(), UPLOAD_PATH)) {
                    $sessionUserData->getFlashBag()->add("alert_success", "File added successfully");
                }
            }
            return $this->app->redirect("/login");
        } catch (UniqueConstraintViolationException $ex) {
            $sessionUserData->getFlashBag()->add('alert_danger', 'Sorry, this email id is already registered!');
            return $this->app->redirect("/register");
        }
    }

    public function doLogin(Request $request) {

        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $loginInfo = $entityManager->getRepository('Entity\User')->findOneBy(array(
            'userEmail' => $request->request->get('loginEmail'),
            'password' => md5($request->request->get('loginPassword'))
        ));

        if (empty($loginInfo)) {
            $sessionUserData->getFlashBag()->add(
                    'alert_danger', 'Sorry, email and password does not match'
            );
            return $this->app->redirect("/login");
        }

        $sessionUserData->set('loggedInUser', $loginInfo->getId());
        return $this->app->redirect('/home');
    }

    public function logout() {
        $sessionUserData = $this->app['session'];
        $sessionUserData->remove('loginSession');
        $sessionUserData->clear();
        return $this->app->redirect("/");
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

}
