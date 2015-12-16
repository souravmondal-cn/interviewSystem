<?php

namespace Controller\Admin;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Entity\Questions;
use Entity\Category;
use Entity\Examination;
use DateTime;
use Controller\Admin\Controller;

class ExamController extends Controller {

    public function examSetting() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entiryManager = $this->app['doctrine'];
        $userEmailRepository = $entiryManager->getRepository('Entity\User');
        $userDetails = $userEmailRepository->findBy(array('is_admin' => 0));

        $questionsRepository = $entiryManager->getRepository('Entity\Questions');
        $questionsDetails = $questionsRepository->findAll();

        $categoryDetails = array();
        //categoryId overlapped in the array key, so it seems distinctly selected
        foreach ($questionsDetails as $questionsDetail) {
            $categoryDetails[$questionsDetail->getCategoryId()->getCId()] = $questionsDetail->getCategoryId()->getCategoryName();
        }

        return $this->app['twig']->render('admin/examsetting.twig', array('userDetails' => $userDetails, 'categories' => $categoryDetails));
    }

    public function examGenerate(Request $request) {
        $postedExamData = $request->request->all();
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        if ($postedExamData['userEmailId'] == '' || $postedExamData['qNumbers'] == '' || empty($postedExamData['qCategory'])) {
            $sessionData->getFlashBag()->add('alert_danger', 'Please fill up every detail carefully!');
            return $this->app->redirect('/examsetting');
        }

        $userEmail = $postedExamData['userEmailId'];
        $qCategoryId = $postedExamData['qCategory'];
        $qNumbers = $postedExamData['qNumbers'];
        $timeout = 60;

        $countCatNum = count($qCategoryId);

        if ($countCatNum > 5 || $countCatNum < 3) {
            $sessionData->getFlashBag()->add('alert_danger', 'Minimum three and maximum five categories should be selected!');
            return $this->app->redirect('/examsetting');
        }

        /* @var $questionRepository \Entity\Questions */
        $questionRepository = $entityManager->getRepository('Entity\Questions');

        $questions = array();
        foreach ($qCategoryId as $cId) {
            $countQuestion = $questionRepository->findBycategoryId($cId);
            $countQNumbers = count($countQuestion);
            if ($qNumbers > $countQNumbers) {
                $sessionData->getFlashBag()->add('alert_danger', 'Not enough questions to generate exam!');
                return $this->app->redirect('/examsetting');
            }

//            manual native query select * from questions where categoryId = 23  order by rand() limit 3
//            $questions = $this->generateQuestions($cId, $qNumbers);
            $questions = $questionRepository->findBy(array('categoryId' => $cId), array(), $qNumbers);
            foreach ($questions as $question) {
                $q[] = $question->getQId();
            }
        }

        $totalQuestions = count($q);
        $totalTimeInSeconds = $totalQuestions * $timeout;
        $allQuestionIds = implode(',', $q);

        try {
            $examination = new Examination();
            $examination->setEmail($userEmail);
            $examination->setQuestions($allQuestionIds);
            $examination->setTotalTime($totalTimeInSeconds);
            $examination->setTotal_Questions($totalQuestions);

            date_default_timezone_set("Asia/Calcutta");
            $examination->setDate_Created(new DateTime());

            $entityManager->persist($examination);
            $entityManager->flush();

            $sessionData->getFlashBag()->add('alert_success', 'Examination set successful!');
            return $this->app->redirect('/adminpanel');
        } catch (NotNullConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add('alert_danger', 'Please fill up all fields');
            return $this->app->redirect('/examsetting');
        }
    }

    public function viewExamDetail($examId) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);

        $emailId = $examDetail->getEmail();
        $submitDetails = $examDetail->getSubmits();
        if ($submitDetails == NULL) {
            $sessionData->getFlashBag()->add('alert_info', 'Examination not completed. No details found');
            return $this->app->redirect('/viewHistory/' . $emailId);
        }

        $submitDetailsArray = json_decode($submitDetails);

        foreach ($submitDetailsArray as $questionId => $checkedOption) {
            $questionDetail = $entityManager->find('Entity\Questions', $questionId);

            $isQualified = $examDetail->getIs_Qualified();
            $totalQuestions = $examDetail->getTotal_Questions();
            $totalAnswers = $examDetail->getCorrect_Answers();
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
                    'emailId' => $emailId,
                    'examId' => $examId,
                    'totalQuestions' => $totalQuestions,
                    'totalAnswers' => $totalAnswers,
                    'dataPercentage' => $dataPercentage,
                    'isQualified' => $isQualified]);
    }

    public function setQualified($examId) {
        $entityManager = $this->app['doctrine'];
        $examDetail = $entityManager->find('Entity\Examination', $examId);
        $examDetail->setIs_Qualified(TRUE);
        $entityManager->persist($examDetail);
        $entityManager->flush();

        return $this->app->redirect('/examdetail/' . $examId);
    }

    public function listExamHistory($email) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $examRepository = $entityManager->getRepository('Entity\Examination');
        $examData = $examRepository->findBy(array('email' => $email));

        return $this->app['twig']->render('admin/examhistory.twig', array('examdata' => $examData, 'email' => $email));
    }

}
