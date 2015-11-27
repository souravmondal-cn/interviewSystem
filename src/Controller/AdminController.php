<?php

namespace Controller;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Entity\Questions;
use \Entity\Category;
use \Entity\User;
use \Entity\Examination;
use DateTime;
use \Doctrine\ORM\Query\ResultSetMapping;
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
        
        if(empty($loginInfo)) {
            $sessionData->getFlashBag()->add('message','Sorry, user email and password does not match or not registered!');
            return $this->app->redirect("/admin");
        }
        
        $sessionData->set('loginAdminSession',true);
        $sessionData->set('loginAdminEmail',$loginDetails['email']);
        return $this->app->redirect('/adminpanel');
    }
    
    public function adminPanel() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app['twig']->render('admin/adminpanel.twig',array('UserEmail' => $adminLogInEmail));
    }
    
    public function questionUpload() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        return $this->app['twig']->render('admin/qaupload.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories, 'formHeading' => 'Question and answer upload'));
    }
    
    public function doUpload(Request $request) {
        $postedData = $request->request->all();
        $category = array('cid' => $postedData['category']);
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        if($postedData['addquestion'] == '' || $postedData['opta'] == '' || $postedData['optc'] == '' || $postedData['optd'] == '' || $postedData['correct'] == '') {
            
            $sessionData->getFlashBag()->add('admin_message','No field should be blank, please fillup correctly.');
            
            return $this->app->redirect("/questionupload");
        }
        
        try{
            if($postedData['questionId'] != '') {
                $question = $entityManager->find('Entity\Questions',$postedData['questionId']);

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

                $sessionData->getFlashBag()->add('admin_message','Question edited successfully');
                
                return $this->app->redirect("/questionlisting");
            }
            else{
        
            $questions = new Questions();
            
            $questions->setQuestion($postedData['addquestion']);
            $questions->setOptionA($postedData['opta']);
            $questions->setOptionB($postedData['optb']);
            $questions->setOptionC($postedData['optc']);
            $questions->setOptionD($postedData['optd']);
            $questions->setAnswer($postedData['correct']);
            
            $QuestionCategory = $entityManager->getRepository('Entity\Category')->find($postedData['category']);
            $questions->setCategoryId($QuestionCategory);

            $entityManager->persist($questions);
            $entityManager->flush();

            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('admin_message','Question uploading successful');
            $adminLogInEmail = $sessionData->get('loginAdminEmail');
            return $this->app->redirect("/questionupload");
            } 
        }
        catch (UniqueConstraintViolationException $ex) {
            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('admin_message','Unique value required');
            return $this->app->redirect("/questionupload");
        }
    }
    
    public function categorySetting() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        
        $result = $categoryRepository->findAll();
        $categories = $result;
        
        //Or uncomment this line
        //$categories = $this->getParentCategory();
        
        return $this->app['twig']->render('admin/category_setting.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories));

    }
    
    public function getParentCategory() {
        $entityManager = $this->app['doctrine'];
        $categoryReposity = $entityManager->getRepository('Entity\Category');
        $result = $categoryRepository->findByparentId(0);
        
        return $result;
    }
    
    public function doAddCategory(Request $request) {
        $postedData = $request->request->all();
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        if($postedData['category'] == 'Choose parent category' || $postedData['category'] == '' || $postedData['subcategory'] == '') {
            $sessionData->getFlashBag()->add('admin_message','No field should be blank, please fillup correctly');
            return $this->app->redirect('/addcategory');
        }
        
        if($postedData['categoryId'] == '') {
            try{
            $category = new Category();
            
            $category->setParentId($postedData['category']);
            $category->setCategoryName($postedData['subcategory']);

            $entityManager = $this->app['doctrine'];
            $entityManager->persist($category);
            $entityManager->flush();

            $sessionData->getFlashBag()->add('admin_message','Category added successfully');
            } catch (UniqueConstraintViolationException $ex) {
                return $this->app->redirect('/addcategory');
            }
        
        }
        else {
            try {
                $entityManager = $this->app['doctrine'];
                $category = $entityManager->find('Entity\Category', $postedData['categoryId']);
                
                $category->setParentId($postedData['category']);
                $category->setCategoryName($postedData['subcategory']);

                $entityManager = $this->app['doctrine'];
                $entityManager->persist($category);
                $entityManager->flush();

                $sessionData = $this->app['session'];
                $sessionData->getFlashBag()->add('admin_message','Category edited successfully');
                
            } catch (UniqueConstraintViolationException $ex) {
                return $this->app['twig']->render('admin/category_setting.twig');
            }
        
            return $this->app->redirect('/category');
            }
        
        return $this->app->redirect('/addcategory');  
    }

    public function addCategory() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        return $this->app['twig']->render('admin/addcategory.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories, 'formHeading' => 'Add Category'));
    }
    
    public function editCategory($id) {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        $categoryDetails = $entityManager->find('Entity\Category',$id);
        
        return $this->app['twig']->render('admin/addcategory.twig',array('categories' => $categories, 'categoryDetails' => $categoryDetails, 'formHeading' => 'Edit Category'));
    }
    
    public function deleteCategory($id) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        $categoryParent = array(
            'parentId' => $id
        );
        $checkCategory = $categoryRepository->findBy($categoryParent);
        if(!empty($checkCategory)){
            $sessionData->getFlashBag()->add('admin_message','Category has sub categories!');
        
            return $this->app->redirect('/category');
        }
        $categoryDetails = $entityManager->find('Entity\Category',$id);
        $entityManager->remove($categoryDetails);
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager->flush();
        
        $sessionData->getFlashBag()->add('admin_message','Category deleted successfully');
        
        return $this->app->redirect('/category');
    }

    public function questionListing() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager = $this->app['doctrine'];
        $questionRepository = $entityManager->getRepository('Entity\Questions');
        
        $questions = $questionRepository->findAll();
        
        return $this->app['twig']->render('admin/question_setting.twig',array('UserEmail' => $adminLogInEmail, 'questions' => $questions));
    }
    
    public function editQuestion($id) {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        $questionDetails = $entityManager->find('Entity\Questions',$id);
        
        return $this->app['twig']->render('admin/qaupload.twig',array('categories' => $categories, 'questionDetails' => $questionDetails, 'formHeading' => 'Question and answer edit'));
    }
    
    public function deleteQuestion($id) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        
        $questionDetails = $entityManager->find('Entity\Questions',$id);
        $entityManager->remove($questionDetails);
        
        $entityManager->flush();
        
        $sessionData->getFlashBag()->add('admin_message','Question deleted successfully');
        
        return $this->app->redirect('/questionlisting');
    }
    
    public function showAdminSettings() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '1'));
        return $this->app['twig']->render('admin/admin_settings.twig',array('userData' => $user, 'pageHeading' => 'Admin'));
    }
    
        public function showUserSettings() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('is_admin' => '0'));
        return $this->app['twig']->render('admin/user_settings.twig',array('userData' => $user, 'pageHeading' => 'User'));
    }
    
    public function addAllUsers($userType) {
        return $this->app['twig']->render('admin/addallusers.twig',array('formHeading' => 'Add ', 'userType' => $userType));
    }
    
    public function checkUserRegistration($email) {
        $entityManager = $this->app['doctrine'];
        $userRepository = $entityManager->getRepository('Entity\User');
        $user = $userRepository->findBy(array('email' => $email, 'is_admin' => '0'));
        
        if(!empty($user)) {
            echo '1';exit;
        }
        else {
            echo '0';exit;
        }
    }
    
    public function newFileUpload($uploadedFile, $fileName) {
        
        if(empty($uploadedFile)){
             return false;   
        }
        
        //Delete previous file of this user
        $fs = new FileSystem();
        if ($fs->exists(__DIR__.'/../../uploads/'.$fileName.'.docx')) {
            $fileFullName = $fileName.'.docx';
        }
        elseif ($fs->exists(__DIR__.'/../../uploads/'.$fileName.'.doc')) {
            $fileFullName = $fileName.'.doc';
        }
        else{
            $fileFullName = $fileName.'.pdf';
        }
        
        if ($fs->exists(__DIR__.'/../../uploads/'.$fileFullName)) {
            unlink(__DIR__.'/../../uploads/'.$fileFullName);
        }
        //delete block ends
        
        $mimeType = $uploadedFile->getMimeType();
        $allowedTypes = array(
            'application/msword',
            'application/pdf'
        );
                
        if(!in_array($mimeType, $allowedTypes)) {
            return false;
        }
        
        $uploadedFile->move(__DIR__.'/../../uploads/', $fileName.'.'.$uploadedFile->guessExtension());

        return true;
    }
    
    public function doAddUser(Request $request, $userType) {
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        
        $postedFormData = $request->request->all();
        if($postedFormData['userName'] == '' || $postedFormData['userEmail'] == '' || $postedFormData['password'] == '' || $postedFormData['isAdmin'] == '') {
            $sessionData->getFlashBag()->add("admin_message", "No field should left blank");
            if($userType == 'User') {
                return $this->app->redirect("/usersetting");
            }
            else {
                return $this->app->redirect("/adminsetting");
            }    
        }
        
        try{
            if($postedFormData['userId'] == '') {
                
                $user = new User();
                
                $user->setUserName($postedFormData['userName']);
                $user->setEmail($postedFormData['userEmail']);
                $user->setPassword(md5($postedFormData['password']));
                $user->setLocation($postedFormData['location']);
                $user->setUser_Address($postedFormData['user_address']);
                $user->setIs_Admin($postedFormData['isAdmin']);
                
                $entityManager->persist($user);
                $entityManager->flush();
                
                if($userType != 'Admin') {
                    $uploadedFile = $request->files->get('uploadedFile');
                    
                    $generatedUserData = $entityManager->getRepository('Entity\User')->findBy(array('email' => $postedFormData['userEmail']));
                    $generatedId = $generatedUserData[0]->getId();
                    
                    if($this->newFileUpload($uploadedFile, $generatedId)) {
                        $sessionData->getFlashBag()->add("admin_message", "File added successfully");
                    }
                    else {
                        $sessionData->getFlashBag()->add("admin_message", "File uploading not done");
                    }
                }

                $sessionData->getFlashBag()->add("admin_message", $userType." added successfully!");
                
                if($userType == 'User') {
                    return $this->app->redirect("/usersetting");
                }
                
                return $this->app->redirect("/adminsetting");
            }
            else {
                $prevPassword = $postedFormData['userPassword'];
                $newPassword = $postedFormData['password'];
                $user = $entityManager->find('Entity\User', $postedFormData['userId']);
                $uploadedFile = $request->files->get('uploadedFile');
                if(!empty($uploadedFile)){
                    $this->newFileUpload($uploadedFile, $postedFormData['userId']);  
                }
        
                $user->setUserName($postedFormData['userName']);
                $user->setEmail($postedFormData['userEmail']);
                
                $user->setIs_Admin($postedFormData['isAdmin']);
                $user->setLocation($postedFormData['location']);
                $user->setUser_Address($postedFormData['user_address']);
                
                if($prevPassword != $newPassword) {
                    $user->setPassword(md5($postedFormData['password']));
                }
                
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionData->getFlashBag()->add('admin_message', $userType.' details edited successfully');
                
                if($userType == 'User') {
                    return $this->app->redirect("/usersetting");
                }
                
                return $this->app->redirect("/adminsetting");
            }
        }catch (UniqueConstraintViolationException $ex){
            $sessionData->getFlashBag()->add("admin_message","Email id is already registered, unique required!");
            
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
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\User');
        $categories = $categoryRepository->findAll();
        
        $userDetails = $entityManager->find('Entity\User',$id);
        
        return $this->app['twig']->render('admin/addallusers.twig',array(
            'userData' => $userDetails, 
            'formHeading' => 'Edit ', 
            'userType' => $userType));
    }
    
    public function deleteUserData($userType, $id) {
        $sessionData = $this->app['session'];
        $entityManager = $this->app['doctrine'];
        
        $userDetails = $entityManager->find('Entity\User',$id);
        $entityManager->remove($userDetails);
        
        $entityManager->flush();
        
        $sessionData->getFlashBag()->add('admin_message',$userType.' deleted from database');
        
        if ($userType == 'User'){
            return $this->app->redirect('/usersetting');
        }
        
        return $this->app->redirect('/adminsetting');
    }

    public function examSetting() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminSession');
        
        if(empty($validAdminSession)) {
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
        
        return $this->app['twig']->render('admin/examsetting.twig',array('userDetails' => $userDetails, 'categories' => $categoryDetails));
    }
    
    public function examGenerate(Request $request) {
        
        $entityManager = $this->app['doctrine'];
        $sessionData = $this->app['session'];
        $postedExamData = $request->request->all();
        $userEmail = $postedExamData['userEmailId'];
        $qCategoryId = $postedExamData['qCategory'];
        $qNumbers = $postedExamData['qNumbers'];
        $timeout = 60;

        if($userEmail == '' || $qNumbers == '' || empty($qCategoryId)) {
            $sessionData->getFlashBag()->add('admin_message', 'Fillup every detail carefully');
            return $this->app->redirect('/examsetting');
        }
        $countCatNum = count($qCategoryId);
        
        if($countCatNum>5 || $countCatNum<3){
            $sessionData->getFlashBag()->add('admin_message', 'Minimum 3 and maximum 5 category should be selected!');
            return $this->app->redirect('/examsetting');
        }
        
        
        $questionRepository = $entityManager->getRepository('Entity\Questions');
        
        $questions = array();
        foreach ($qCategoryId as $cId) {
            $countQuestion = $questionRepository->findBycategoryId($cId);
            $countQNumbers = count($countQuestion);
            if($qNumbers > $countQNumbers) {
              $sessionData->getFlashBag()->add('admin_message', 'Not enough questions to generate exam!');
              return $this->app->redirect('/examsetting');
            }
            
//            manual native query select * from questions where categoryId = 23  order by rand() limit 3
//            $questions = $this->generateQuestions($cId, $qNumbers);
            $questions = $questionRepository->findBy(array('categoryId' => $cId),array(),$qNumbers);
            foreach ($questions as $question) {
                $q[] = $question->getQId();
            } 
        }
        
        $totalQuestions = count($q);
        $totalTimeInSeconds = $totalQuestions*$timeout;
        $allQuestionIds = implode(',', $q);
        
        try{
        $examination = new Examination();
        $examination->setEmail($userEmail);
        $examination->setQuestions($allQuestionIds);
        $examination->setTotalTime($totalTimeInSeconds);
        $examination->setTotal_Questions($totalQuestions);
        
        date_default_timezone_set("Asia/Calcutta");
        $examination->setDate_Created(new DateTime());
        
        $entityManager->persist($examination);
        $entityManager->flush();
        
        $sessionData->getFlashBag()->add('admin_message', 'Examination set successful!');
        return $this->app->redirect('/adminpanel');
        }
        catch(NotNullConstraintViolationException $ex){
            $sessionData->getFlashBag()->add('admin_message', 'Fill up all fields');
            return $this->app->redirect('/examsetting');
        }
    }
    
    public function generateQuestions($categoryId,$limit) {
        $resultSetMapping = new ResultSetMapping();
        $entityManager = $this->app['doctrine'];
        $query = $entityManager->createNativeQuery('select * from questions where categoryId = ?  order by rand limit ?', $resultSetMapping);
        $query->setParameter(1,$categoryId);
        $query->setParameter(2,$limit);
        $result = $query->getResult();
        return $result;
    }
    
    public function downloadFile($filename) {
        $sessionData = $this->app['session'];
//        $rootPath = $request->getBaseUrl();
//        $basePath = $rootPath.'/uploads';
        $file = __DIR__.'/../../uploads/'.$filename;

        // check if file exists
        $fs = new FileSystem();
        if ($fs->exists($file.'.docx')) {
            $filePath = $file.'.docx';
        }
        elseif ($fs->exists($file.'.doc')) {
            $filePath = $file.'.doc';
        }
        else{
            $filePath = $file.'.pdf';
        }
        
        if (!$fs->exists($filePath)) {
            $sessionData->getFlashBag()->add('admin_message', 'File not found!');
            return $this->app->redirect('/usersetting');
        }

        // prepare BinaryFileResponse
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename,
            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }
    public function listExamHistory($email) {
        $entityManager = $this->app['doctrine'];
        $examRepository = $entityManager->getRepository('Entity\Examination');
        $examData = $examRepository->findBy(array('email' => $email));

        return $this->app['twig']->render('admin/examhistory.twig',array('examdata' => $examData, 'email' => $email));
    }
    
    public function adminLogout() {
        $sessionData = $this->app['session'];
        $sessionData->clear();
        return $this->app->redirect("/admin");
    }
}