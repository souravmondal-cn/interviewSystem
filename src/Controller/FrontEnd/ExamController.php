<?php

namespace Controller\FrontEnd;

use \Symfony\Component\HttpFoundation\Request;
use Controller\Controller;
use \DateTime;

class ExamController extends Controller {

    public function getExamData($userId) {
        $entityManager = $this->app['doctrine'];
        $examData = $entityManager->getRepository('Entity\Examination')->findOneBy(
            array(
                'userId' => $userId,
                'completed' => null
            )
        );
        return $examData;
    }

    public function getExamSuccessStatus($examId) {

        $entityManager = $this->app['doctrine'];
        $success = false;

        $successData = $entityManager->getRepository('Entity\Examination')->findOneBy(
            ['userId' => $examId],
            ['id' => 'desc']
        );

        if (!empty($successData)) {
            $isQualified = $successData->getIsQualified();

            if (!empty($isQualified)) {
                $success = true;
            }
        }

        return $success;
    }

    public function examStart($userId) {

        $sessionUserData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $validSession = $sessionUserData->get('userSession');
        if (empty($validSession)) {
            return $this->app->redirect(BASEPATH."/dashboard");
        }

        $examData = $this->getExamData($userId);

        if (empty($examData)) {

            $sessionUserData->getFlashBag()->add('user_message', 'Examination Over');
            return $this->app->redirect(BASEPATH."/dashboard");
        }

        $getQuestions = $examData->getQuestions();
        $questions = explode(',', $getQuestions);

        $questionRepository = $entityManager->getRepository('Entity\Questions');
        $questionDetails = $questionRepository->findBy(['id' => $questions]);

        $this->setExamDetailsToSession($examData, $questionDetails);

        return $this->app->redirect(BASEPATH.'/displayQuestion');
    }

    public function setExamDetailsToSession($examData, $questionDetails) {

        $examId = $examData->getId();
        $totaltime = $examData->getTotalTime();
        $totalQuestions = $examData->getTotalQuestions();
        $questionPointer = 0;
        $validExam = true;

        $sessionUserData = $this->app['session'];

        $sessionUserData->set('questionPointer', $questionPointer);
        $sessionUserData->set('validExam', $validExam);
        $sessionUserData->set('examId', $examId);
        $sessionUserData->set('totalQuestions', $totalQuestions);
        $sessionUserData->set('totaltime', $totaltime);
        $sessionUserData->set('questionData', $questionDetails);

        return;
    }

    public function displayQuestion() {

        $sessionData = $this->app['session'];
        $validExam = $sessionData->get('validExam');
        $totaltime = $sessionData->get('totaltime');
        header("Refresh: $totaltime; url=/examsubmit");

        if ($validExam == false) {
            return $this->app->redirect(BASEPATH.'/examsubmit');
        }

        $sessionData->set('validExam', false);

        $logInEmail = $sessionData->get('loginEmail');
        $loggerName = $sessionData->get('loggerName');
        $questionData = $sessionData->get('questionData');
        $questionPointer = $sessionData->get('questionPointer');

        if (!$this->checkQuestionPointerStatus()) {
            return $this->app->redirect(BASEPATH.'/dashboard');
        }

        $displayQuestion = $questionData[$questionPointer];

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
        $questionPointer = $sessionUserData->get('questionPointer');

        $questionId = $request->request->get('qid');
        $usersAnswer = (!empty($request->request->get('answer')) ? $request->request->get('answer') : '');

        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $qa[$questionId] = $usersAnswer;

        $usersInputJson = $this->getUsersInputAsJsonData($qa, $examId);
        $examDetail->setUsersInput($usersInputJson);

        $this->setScore($examId, $questionId, $usersAnswer);

        unset($questionData[$questionPointer]);
        $sessionUserData->set('questionData', $questionData);

        $newQuestionPointer = $questionPointer + 1;
        $sessionUserData->set('questionPointer', $newQuestionPointer);

        date_default_timezone_set("Asia/Kolkata");
        $examDetail->setCompleted(new DateTime());
        $entityManager->persist($examDetail);
        $entityManager->flush();

        $sessionUserData->set('validExam', true);
        return $this->app->redirect(BASEPATH.'/displayQuestion');
    }

    public function getUsersInputAsJsonData($usersSubmit, $examId) {

        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $previousSubmits = $examDetail->getUsersInput();

        $json = json_encode($usersSubmit);
        if ($previousSubmits == '' || $previousSubmits == null) {
            $jsonData = $json;
        } else {
            $jsonData = rtrim($previousSubmits, '}') . ',' . ltrim($json, '{');
        }

        return $jsonData;
    }

    public function setScore($examId, $questionId, $usersAnswer) {

        $entityManager = $this->app['doctrine'];
        $questionDetail = $entityManager->find('Entity\Questions', $questionId);
        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $originalAnswer = $questionDetail->getAnswer();

        if ($originalAnswer == $usersAnswer) {
            $scorePoint = 1;
        } else {
            $scorePoint = 0;
        }

        $correctAnswersCount = $examDetail->getCorrectAnswersCount();
        $newcorrectAnswersCount = $correctAnswersCount + $scorePoint;
        $examDetail->setCorrectAnswersCount($newcorrectAnswersCount);

        return;
    }

    public function checkQuestionPointerStatus() {

        $sessionData = $this->app['session'];
        $questionPointer = $sessionData->get('questionPointer');
        $totalQuestions = $sessionData->get('totalQuestions');

        if ($questionPointer === $totalQuestions) {

            $this->removeExamDetailsFromSession();
            $sessionData->getFlashBag()->add('user_message', 'Examination finished');

            return false;
        } else {

            return true;
        }
    }

    public function removeExamDetailsFromSession() {

        $sessionData = $this->app['session'];
        $sessionData->remove('examId');
        $sessionData->remove('totalQuestions');
        $sessionData->remove('questionData');
        $sessionData->remove('questionPointer');
        $sessionData->remove('totaltime');

        return;
    }

    public function forceSubmit() {
        $entityManager = $this->app['doctrine'];
        $sessionUserData = $this->app['session'];
        $examId = $sessionUserData->get('examId');

        if (empty($examId)) {
            return $this->app->redirect(BASEPATH.'/dashboard');
        }

        $this->removeExamDetailsFromSession();

        $examDetail = $entityManager->find('Entity\Examination', $examId);
        date_default_timezone_set("Asia/Kolkata");
        $examDetail->setCompleted(new DateTime());
        $entityManager->persist($examDetail);
        $entityManager->flush();
        $sessionUserData->getFlashBag()->add('user_message', 'Examination finished');
        return $this->app->redirect(BASEPATH.'/dashboard');
    }

}
