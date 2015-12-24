<?php

namespace Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Entity\Questions;
use Entity\Category;
use Controller\Controller;

class AdminSettingsController extends Controller {

    public function questionListing() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager = $this->app['doctrine'];
        $questionRepository = $entityManager->getRepository('Entity\Questions');

        $questions = $questionRepository->findAll();

        return $this->app['twig']->render('admin/question_setting.twig', array('UserEmail' => $adminLogInEmail, 'questions' => $questions, 'pageTitle' => 'Question Settings'));
    }

    public function questionUpload() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        return $this->app['twig']->render(
            'admin/qaupload.twig',
            array(
                'UserEmail' => $adminLogInEmail,
                'categories' => $categories,
                'formHeading' => 'Question and answer upload',
                'pageTitle' => 'Question - Answer Upload'
            )
        );
    }

    public function doUpload(Request $request) {
        $postedData = $request->request->all();

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        if (
                $postedData['category'] == ''||
                $postedData['difficultyLevel'] == ''||
                $postedData['addquestion'] == '' ||
                $postedData['opta'] == '' ||
                $postedData['optc'] == '' ||
                $postedData['optd'] == '' ||
                $postedData['correct'] == ''
        ) {

            $sessionData->getFlashBag()->add('alert_danger', 'No field should be blank, please fillup correctly.');

            return $this->app->redirect(BASEPATH."/questionupload");
        }

        if ($postedData['questionId'] != '') {
            $question = $entityManager->find('Entity\Questions', $postedData['questionId']);

            $sessionData->getFlashBag()->add('alert_info', 'Question edited successfully');
        } else {
            $question = new Questions();

            $sessionData->getFlashBag()->add('alert_success', 'Question uploaded successfully');
        }

        try {
            $question->setDifficultyLevel($postedData['difficultyLevel']);
            $question->setQuestion($postedData['addquestion']);
            $question->setOptionA($postedData['opta']);
            $question->setOptionB($postedData['optb']);
            $question->setOptionC($postedData['optc']);
            $question->setOptionD($postedData['optd']);
            $question->setAnswer($postedData['correct']);

            $questionCategory = $entityManager->getRepository('Entity\Category')->find($postedData['category']);
            $question->setCategoryId($questionCategory);

            $entityManager->persist($question);
            $entityManager->flush();
            return $this->app->redirect(BASEPATH."/questionlisting");
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('alert_danger', 'Unique value required');
            return $this->app->redirect(BASEPATH."/questionlisting");
        }
    }

    public function editQuestion($id) {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        $questionDetails = $entityManager->find('Entity\Questions', $id);

        return $this->app['twig']->render(
            'admin/qaupload.twig',
            array(
                'categories' => $categories,
                'questionDetails' => $questionDetails,
                'formHeading' => 'Question and answer edit',
                'pageTitle' => 'Question - Answer Edit'
                )
        );
    }

    public function deleteQuestion($id) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $questionDetails = $entityManager->find('Entity\Questions', $id);
        $entityManager->remove($questionDetails);

        $entityManager->flush();

        $sessionData->getFlashBag()->add('alert_success', 'Question deleted successfully');

        return $this->app->redirect(BASEPATH.'/questionlisting');
    }

    public function categorySetting() {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $categoryRepository = $entityManager->getRepository('Entity\Category');

        $result = $categoryRepository->findAll();
        $categories = $result;

        return $this->app['twig']->render(
            'admin/category_setting.twig',
            array(
                'UserEmail' => $adminLogInEmail,
                'categories' => $categories,
                'pageTitle' => 'Category Settings'
            )
        );
    }

    public function addCategory() {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        return $this->app['twig']->render(
            'admin/addcategory.twig',
            array(
                'UserEmail' => $adminLogInEmail,
                'categories' => $categories,
                'formHeading' => 'Add Category',
                'pageTitle' => 'Add Category'
            )
        );
    }

    public function doAddCategory(Request $request) {
        $postedData = $request->request->all();
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        if ($postedData['category'] == 'Choose parent category' || $postedData['category'] == '' || $postedData['subcategory'] == '') {
            $sessionData->getFlashBag()->add('alert_danger', 'No field should be blank, please fillup correctly');
            return $this->app->redirect(BASEPATH.'/addcategory');
        }

        if ($postedData['categoryId'] == '') {
            $category = new Category();
            $sessionData->getFlashBag()->add('alert_success', 'Category added successfully');
        } else {
            $category = $entityManager->find('Entity\Category', $postedData['categoryId']);
            $sessionData->getFlashBag()->add('alert_info', 'Category edited successfully');
        }

        try {
            $category->setParentId($postedData['category']);
            $category->setCategoryName($postedData['subcategory']);
            $entityManager->persist($category);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add('alert_danger', 'Something went wrong!');
            return $this->app->redirect(BASEPATH.'/addcategory');
        }

        return $this->app->redirect(BASEPATH.'/category');
    }

    public function editCategory($id) {
        if ($this->checkAdminSession() == false) {
            return $this->app->redirect(BASEPATH."/admin");
        }

        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        $categoryDetails = $entityManager->find('Entity\Category', $id);

        $details = ['categories' => $categories, 'categoryDetails' => $categoryDetails, 'formHeading' => 'Edit Category', 'pageTitle' => 'Edit Category'];
        return $this->app['twig']->render('admin/addcategory.twig', $details);
    }

    public function deleteCategory($id) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');

        $categoryParent = array(
            'parentId' => $id
        );
        $checkCategory = $categoryRepository->findBy($categoryParent);

        if (!empty($checkCategory)) {
            $sessionData->getFlashBag()->add('alert_danger', 'Category has sub categories!');

            return $this->app->redirect(BASEPATH.'/category');
        }

        $categoryDetails = $entityManager->find('Entity\Category', $id);
        $entityManager->remove($categoryDetails);

        $entityManager->flush();

        $sessionData->getFlashBag()->add('alert_success', 'Category deleted successfully');

        return $this->app->redirect(BASEPATH.'/category');
    }

}
