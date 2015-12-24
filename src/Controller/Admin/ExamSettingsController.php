<?php

namespace Controller\Admin;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Entity\Examination;
use Controller\Controller;

class ExamSettingsController extends Controller {

    public function examSetting() {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entiryManager = $this->app['doctrine'];
        $userEmailRepository = $entiryManager->getRepository('Entity\User');
        $userDetails = $userEmailRepository->findBy(array('isAdmin' => 0));

        $questionsRepository = $entiryManager->getRepository('Entity\Questions');
        $questionsDetails = $questionsRepository->findAll();

        $categoryDetails = array();
        //categoryId overlapped in the array key, so it seems distinctly selected
        foreach ($questionsDetails as $questionsDetail) {
            $categoryDetails[$questionsDetail->getCategoryId()->getId()] = $questionsDetail->getCategoryId()->getCategoryName();
        }

        return $this->app['twig']->render('admin/examsetting.twig', array('userDetails' => $userDetails, 'categories' => $categoryDetails, 'pageTitle' => 'Exam Settings'));
    }

    public function examGenerate(Request $request) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        if (
            $request->request->get('userEmailId') == '' ||
            $request->request->get('qNumbers') == '' ||
            empty($request->request->get('qCategory'))
        ) {
            $sessionData->getFlashBag()->add('alert_danger', 'Please fill up every detail carefully!');
            return $this->app->redirect(BASEPATH.'/examsetting');
        }

        $userEmail = $request->request->get('userEmailId');
        $qCategoryId = $request->request->get('qCategory');
        $countCatNum = count($qCategoryId);
        $qNumbers = $request->request->get('qNumbers');
        $timeout = QUESTION_TIMEOUT;

        if ($countCatNum > MAX_CATEGORY || $countCatNum < MIN_CATEGORY) {

            $sessionData->getFlashBag()->add('alert_danger', 'Minimum '.MIN_CATEGORY.' and maximum '.MAX_CATEGORY.' categories should be selected!');
            return $this->app->redirect(BASEPATH.'/examsetting');
        }

        /* @var $questionRepository \Entity\Questions */
        $questionRepository = $entityManager->getRepository('Entity\Questions');

        $questions = array();
        foreach ($qCategoryId as $cId) {

            $countQuestion = $questionRepository->findBycategoryId($cId);
            $countQNumbers = count($countQuestion);
            if ($qNumbers > $countQNumbers) {

                $sessionData->getFlashBag()->add('alert_danger', 'Not enough questions to generate exam!');
                return $this->app->redirect(BASEPATH.'/examsetting');
            }

            $questions = $questionRepository->findBy(array('categoryId' => $cId), array(), $qNumbers);
            foreach ($questions as $question) {
                $questionIdsArray[] = $question->getId();
            }
        }

        $totalQuestions = count($questionIdsArray);
        $totalTimeInSeconds = $totalQuestions * $timeout;

        $this->setValidDataForExamination(
            $userEmail,
            $questionIdsArray,
            $totalTimeInSeconds,
            $totalQuestions
        );
        
        return $this->app->redirect(BASEPATH.'/admin');
    }

    public function setValidDataForExamination(
    $userEmail, $questionIdsArray, $totalTimeInSeconds, $totalQuestions) {

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $questionIds = implode(',', $questionIdsArray);
        $examination = new Examination();

        $userRepository = $entityManager->getRepository('Entity\User');
        $userDetail = $userRepository->findOneBy(array('userEmail' => $userEmail));

        $examination->setUserId($userDetail);
        $examination->setQuestions($questionIds);
        $examination->setTotalTime($totalTimeInSeconds);
        $examination->setTotalQuestions($totalQuestions);
        try {

            $entityManager->persist($examination);
            $entityManager->flush();

            $sessionData->getFlashBag()->add('alert_success', 'Examination set successful!');
        } catch (NotNullConstraintViolationException $ex) {

            $sessionData->getFlashBag()->add('alert_danger', 'Please fill up all fields');
        }
        
        return;
    }

    public function viewExamDetail($examId) {
        
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $userId = $examDetail->getUserId()->getId();
        $emailId = $examDetail->getUserId()->getUserEmail();
        $submitDetails = $examDetail->getUsersInput();
        if ($submitDetails == null) {
            $sessionData->getFlashBag()->add('alert_info', 'Examination not completed. No details found');
            return $this->app->redirect(BASEPATH.'/viewHistory/' . $emailId);
        }

        $submitDetailsArray = json_decode($submitDetails);

        foreach ($submitDetailsArray as $questionId => $checkedOption) {
            $questionDetail = $entityManager->find('Entity\Questions', $questionId);

            $isQualified = $examDetail->getIsQualified();
            $totalQuestions = $examDetail->getTotalQuestions();
            $totalAnswers = $examDetail->getCorrectAnswersCount();
            $dataPercentage = ($totalAnswers / $totalQuestions) * 100;
            $optionValueA = $questionDetail->getOptionA();
            $optionValueB = $questionDetail->getOptionB();
            $optionValueC = $questionDetail->getOptionC();
            $optionValueD = $questionDetail->getOptionD();

            if ($checkedOption == 'option_a') {
                $submitAnswer = $optionValueA;
            } elseif ($checkedOption == 'option_b') {
                $submitAnswer = $optionValueB;
            } elseif ($checkedOption == 'option_c') {
                $submitAnswer = $optionValueC;
            } elseif ($checkedOption == 'option_d') {
                $submitAnswer = $optionValueD;
            }

            $examSubmitDetail[] = [
                'submitQid' => $questionId,
                'submitQuestion' => $questionDetail->getQuestion(),
                'optionA' => $optionValueA,
                'optionB' => $optionValueB,
                'optionC' => $optionValueC,
                'optionD' => $optionValueD,
                'correctAnswer' => $questionDetail->getAnswer(),
                'submitAnswer' => $submitAnswer,
            ];
        }

        return $this->app['twig']->render('admin/examdetail.twig', [
                    'examSubmitData' => $examSubmitDetail,
                    'userId' => $userId,
                    'emailId' => $emailId,
                    'examId' => $examId,
                    'totalQuestions' => $totalQuestions,
                    'totalAnswers' => $totalAnswers,
                    'dataPercentage' => $dataPercentage,
                    'isQualified' => $isQualified,
                    'pageTitle' => 'Examination Detail']);
    }

    public function setQualified($examId) {
        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);
        $examDetail->setIsQualified(true);
        $entityManager->persist($examDetail);
        $entityManager->flush();

        return $this->app->redirect(BASEPATH.'/examdetail/' . $examId);
    }

    public function listExamHistory($emailId) {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        
        $userDetails = $entityManager->getRepository('Entity\User')->findOneBy(['userEmail' => $emailId]);
        $userId = $userDetails->getId();
        $examRepository = $entityManager->getRepository('Entity\Examination');
        $examData = $examRepository->findBy(array('userId' => $userId));

        return $this->app['twig']->render('admin/examhistory.twig', array('examdata' => $examData, 'email' => $emailId, 'pageTitle' => 'Examination History'));
    }

}
