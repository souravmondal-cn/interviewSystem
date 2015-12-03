<?php

namespace Controller;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Entity\Questions;
use \Entity\Category;
use \Entity\User;
use \Entity\Examination;
use DateTime;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminController {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function loginAdmin() {
        return $this->app['twig']->render('admin/adminlogin.twig');
    }

    public function doLoginAdmin(Request $request) {
        $postedLogInData = $request->request->all();

        $sessionData = $this->app['session'];

        $loginDetails = array(
            'email' => $postedLogInData['loginEmail'],
            'password' => md5($postedLogInData['loginPassword']),
            'is_admin' => '1'
        );

        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\User')->findBy($loginDetails);

        if (empty($loginInfo)) {
            $sessionData->getFlashBag()->add('alert_danger', 'Sorry, email id and password does not matched!');
            return $this->app->redirect("/admin");
        }

        $sessionData->set('loginAdminSession', true);
        $sessionData->set('loginAdminEmail', $loginDetails['email']);
        $sessionData->getFlashBag()->add('alert_success', 'Welcome to admin panel');
        return $this->app->redirect('/adminpanel');
    }

    public function adminPanel() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app['twig']->render('admin/adminpanel.twig', array('UserEmail' => $adminLogInEmail));
    }

    public function questionUpload() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        return $this->app['twig']->render('admin/qaupload.twig', array('UserEmail' => $adminLogInEmail, 'categories' => $categories, 'formHeading' => 'Question and answer upload'));
    }

    public function doUpload(Request $request) {
        $postedData = $request->request->all();

        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $adminLogInEmail = $sessionData->get('loginAdminEmail');

        if ($postedData['addquestion'] == '' || $postedData['opta'] == '' || $postedData['optc'] == '' || $postedData['optd'] == '' || $postedData['correct'] == '') {

            $sessionData->getFlashBag()->add('alert_danger', 'No field should be blank, please fillup correctly.');

            return $this->app->redirect("/questionupload");
        }

        if ($postedData['questionId'] != '') {
            $question = $entityManager->find('Entity\Questions', $postedData['questionId']);

            $sessionData->getFlashBag()->add('alert_info', 'Question edited successfully');
        } else {
            $question = new Questions();

            $sessionData->getFlashBag()->add('alert_success', 'Question uploaded successfully');
        }

        try {
            $question->setQuestion($postedData['addquestion']);
            $question->setOptionA($postedData['opta']);
            $question->setOptionB($postedData['optb']);
            $question->setOptionC($postedData['optc']);
            $question->setOptionD($postedData['optd']);
            $question->setAnswer($postedData['correct']);

            $QuestionCategory = $entityManager->getRepository('Entity\Category')->find($postedData['category']);
            $question->setCategoryId($QuestionCategory);

            $entityManager->persist($question);
            $entityManager->flush();
            return $this->app->redirect("/questionlisting");
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('alert_danger', 'Unique value required');
            return $this->app->redirect("/questionlisting");
        }
    }

    public function categorySetting() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $categoryRepository = $entityManager->getRepository('Entity\Category');

        $result = $categoryRepository->findAll();
        $categories = $result;

        return $this->app['twig']->render('admin/category_setting.twig', array('UserEmail' => $adminLogInEmail, 'categories' => $categories));
    }

    public function doAddCategory(Request $request) {
        $postedData = $request->request->all();
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        if ($postedData['category'] == 'Choose parent category' || $postedData['category'] == '' || $postedData['subcategory'] == '') {
            $sessionData->getFlashBag()->add('alert_danger', 'No field should be blank, please fillup correctly');
            return $this->app->redirect('/addcategory');
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
            return $this->app->redirect('/addcategory');
        }

        return $this->app->redirect('/category');
    }

    public function addCategory() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        return $this->app['twig']->render('admin/addcategory.twig', array('UserEmail' => $adminLogInEmail, 'categories' => $categories, 'formHeading' => 'Add Category'));
    }

    public function editCategory($id) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        $categoryDetails = $entityManager->find('Entity\Category', $id);

        $details = ['categories' => $categories, 'categoryDetails' => $categoryDetails, 'formHeading' => 'Edit Category'];
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

            return $this->app->redirect('/category');
        }

        $categoryDetails = $entityManager->find('Entity\Category', $id);
        $entityManager->remove($categoryDetails);

        $entityManager->flush();

        $sessionData->getFlashBag()->add('alert_success', 'Category deleted successfully');

        return $this->app->redirect('/category');
    }

    public function questionListing() {
        $sessionData = $this->app['session'];
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager = $this->app['doctrine'];
        $questionRepository = $entityManager->getRepository('Entity\Questions');

        $questions = $questionRepository->findAll();

        return $this->app['twig']->render('admin/question_setting.twig', array('UserEmail' => $adminLogInEmail, 'questions' => $questions));
    }

    public function editQuestion($id) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();

        $questionDetails = $entityManager->find('Entity\Questions', $id);

        return $this->app['twig']->render('admin/qaupload.twig', array('categories' => $categories, 'questionDetails' => $questionDetails, 'formHeading' => 'Question and answer edit'));
    }

    public function deleteQuestion($id) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $questionDetails = $entityManager->find('Entity\Questions', $id);
        $entityManager->remove($questionDetails);

        $entityManager->flush();

        $sessionData->getFlashBag()->add('alert_success', 'Question deleted successfully');

        return $this->app->redirect('/questionlisting');
    }

    public function showAdminSettings() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '1'));
        return $this->app['twig']->render('admin/admin_settings.twig', array('userData' => $user, 'pageHeading' => 'Admin'));
    }

    public function showUserSettings() {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '0'));
        return $this->app['twig']->render('admin/user_settings.twig', array('userData' => $user, 'pageHeading' => 'User'));
    }

    public function addAllUsers($userType) {
        return $this->app['twig']->render('admin/addallusers.twig', array('formHeading' => 'Add ', 'userType' => $userType));
    }

    public function newFileUpload($uploadedFile, $fileName) {

        //Delete previous file of this user
        $fs = new FileSystem();
        if ($fs->exists(__DIR__ . '/../../uploads/' . $fileName . '.docx')) {
            $fileFullName = $fileName . '.docx';
        } elseif ($fs->exists(__DIR__ . '/../../uploads/' . $fileName . '.doc')) {
            $fileFullName = $fileName . '.doc';
        } else {
            $fileFullName = $fileName . '.pdf';
        }

        if ($fs->exists(__DIR__ . '/../../uploads/' . $fileFullName)) {
            unlink(__DIR__ . '/../../uploads/' . $fileFullName);
        }
        //delete block ends

        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );

        if (!in_array($mimeType, $allowedTypes)) {
            $returnStatement = FALSE;
        }

        $uploadedFile->move(__DIR__ . '/../../uploads/', $fileName . '.' . $uploadedFile->guessExtension());
        $returnStatement = TRUE;

        return $returnStatement;
    }

    public function doAddUser(Request $request, $userType) {

        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];

        $postedFormData = $request->request->all();
        $prevPassword = $postedFormData['userPassword'];
        $newPassword = $postedFormData['password'];

        if ($postedFormData['userName'] == '' || $postedFormData['userEmail'] == '' || $postedFormData['password'] == '' || $postedFormData['isAdmin'] == '') {
            $sessionData->getFlashBag()->add("alert_danger", "No field should left blank");
            return $this->app->redirect("/adduser/" . $userType);
        }

        if ($postedFormData['userId'] == '') {

            $user = new User();
            $sessionData->getFlashBag()->add("alert_success", $userType . " added successfully!");
        } else {

            $user = $entityManager->find('Entity\User', $postedFormData['userId']);
            $sessionData->getFlashBag()->add('alert_info', $userType . ' details edited successfully');
        }

        try {
            $user->setUserName($postedFormData['userName']);
            $user->setEmail($postedFormData['userEmail']);

            $user->setIs_Admin($postedFormData['isAdmin']);
            $user->setLocation($postedFormData['location']);
            $user->setUser_Address($postedFormData['user_address']);

            if ($prevPassword != $newPassword) {
                $user->setPassword(md5($postedFormData['password']));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $uploadedFile = $request->files->get('uploadedFile');

            if ($userType != 'Admin' && !empty($uploadedFile)) {

                $generatedUserData = $entityManager->getRepository('Entity\User')->findBy(array('email' => $postedFormData['userEmail']));
                $generatedId = $generatedUserData[0]->getId();

                if ($this->newFileUpload($uploadedFile, $generatedId)) {
                    $sessionData->getFlashBag()->add("alert_success", "File added successfully");
                }
            }

            if ($userType == 'User') {
                $reDirectUrl = "/usersetting";
            } else {
                $reDirectUrl = "/adminsetting";
            }

            return $this->app->redirect($reDirectUrl);
        } catch (UniqueConstraintViolationException $ex) {
            $sessionData->getFlashBag()->add("alert_danger", "Email id is already registered, unique required!");

            $userData = ['is_admin' => $postedFormData['isAdmin'],
                'username' => $postedFormData['userName'],
                'email' => $postedFormData['userEmail'],
                'location' => $postedFormData['location'],
                'user_address' => $postedFormData['user_address'],
                'password' => $postedFormData['password']
            ];
            return $this->app['twig']->render('admin/addallusers.twig', array(
                        'userData' => $userData,
                        'formHeading' => 'Edit ',
                        'userType' => $userType));
        }
    }

    public function editUserData($userType, $id) {
        if ($this->checkAdminSession() == FALSE) {
            return $this->app->redirect("/admin");
        }

        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\User');
        $categories = $categoryRepository->findAll();

        $userDetails = $entityManager->find('Entity\User', $id);

        return $this->app['twig']->render('admin/addallusers.twig', array(
                    'userData' => $userDetails,
                    'formHeading' => 'Edit ',
                    'userType' => $userType));
    }

    public function deleteUserData($userType, $id) {
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

        $userDetails = $entityManager->find('Entity\User', $id);
        $entityManager->remove($userDetails);

        $entityManager->flush();

        $sessionData->getFlashBag()->add('alert_success', $userType . ' deleted from database');

        if ($userType == 'User') {
            $redirectUrl = '/usersetting';
        } else {
            $redirectUrl = '/adminsetting';
        }

        return $this->app->redirect($redirectUrl);
    }

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

//    public function generateQuestions($categoryId,$limit) {
//        $resultSetMapping = new ResultSetMapping();
//        $entityManager = $this->app['doctrine'];
//        $query = $entityManager->createNativeQuery('select * from questions where categoryId = ?  order by rand limit ?', $resultSetMapping);
//        $query->setParameter(1,$categoryId);
//        $query->setParameter(2,$limit);
//        $result = $query->getResult();
//        return $result;
//    }

    public function downloadFile($filename) {
        $sessionData = $this->app['session'];
        $file = __DIR__ . '/../../uploads/' . $filename;

        // check if file exists
        $fs = new FileSystem();
        if ($fs->exists($file . '.docx')) {
            $filePath = $file . '.docx';
        } elseif ($fs->exists($file . '.doc')) {
            $filePath = $file . '.doc';
        } else {
            $filePath = $file . '.pdf';
        }

        if (!$fs->exists($filePath)) {
            $sessionData->getFlashBag()->add('alert_danger', 'File not found!');
            return $this->app->redirect('/usersetting');
        }
        // prepare BinaryFileResponse
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE, $filename, iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }

    public function listExamHistory($email) {
        $entityManager = $this->app['doctrine'];
        $examRepository = $entityManager->getRepository('Entity\Examination');
        $examData = $examRepository->findBy(array('email' => $email));

        return $this->app['twig']->render('admin/examhistory.twig', array('examdata' => $examData, 'email' => $email));
    }

    public function adminLogout() {
        $sessionData = $this->app['session'];
        $sessionData->clear();
        return $this->app->redirect("/admin");
    }

    public function checkAdminSession() {
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];

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
