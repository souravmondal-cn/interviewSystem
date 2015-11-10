<?php

namespace Controller;
use Entity\User;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Entity\Questions;
use \Entity\Category;

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
            'password' => md5($postedLogInData['loginPassword'])  
        );
        
        $entityManager = $this->app['doctrine'];
        $loginInfo = $entityManager->getRepository('Entity\Admin')->findBy($loginDetails);
        
        if(empty($loginInfo)) {
            $sessionData->getFlashBag()->add('message','Sorry, user email and password does not match!');
            return $this->app->redirect("/admin");
        }
        
        $sessionData->set('loginAdminSession',true);
        $sessionData->set('loginAdminEmail',$loginDetails['email']);
        return $this->app->redirect('/adminpanel');
    }
    
    public function adminPanel() {
        $sessionData = $this->app['session'];
        $validAdminSession = $sessionData->get('loginAdminEmail');
        
        if(empty($validAdminSession)) {
            return $this->app->redirect("/admin");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app['twig']->render('admin/adminpanel.twig',array('UserEmail' => $adminLogInEmail));
    }
    
    public function questionUpload() {
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        return $this->app['twig']->render('admin/qaupload.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories));
    }
    
    public function doUpload(Request $request) {
        $postedData = $request->request->all();
        $category = array('cid' => $postedData['category']);
        
        $entityManager = $this->app['doctrine'];
        
        try{
            $questions = new Questions();
            
            $questions->setQuestion($postedData['addquestion']);
            $questions->setOptionA($postedData['opta']);
            $questions->setOptionB($postedData['optb']);
            $questions->setOptionC($postedData['optc']);
            $questions->setOptionD($postedData['optd']);
            $questions->setAnswer($postedData['correct']);
            $questions->setCategoryId($postedData['category']);
            
            $entityManager->persist($questions);
            $entityManager->flush();

            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('admin_message','Question uploading successful');
        } catch (UniqueConstraintViolationException $ex) {
            return $this->app->redirect("/questionupload");
        }
        
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        return $this->app->redirect("/questionupload");
    }
    
    public function categorySetting() {
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        
        $result = $categoryRepository->findAll();
        $categories = $result;
        
        //Or uncomment this line
        //$categories = $this->getCategoryTreeForParentId();
        
        return $this->app['twig']->render('admin/catsetting.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories));

    }
    
    public function getCategoryTreeForParentId($parent_id = 0) {
        $categories = array();
        
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        
        $result = $categoryRepository->findByparentId($parent_id);
        foreach ($result as $mainCategory) {
            $category = array();
            $category['cid'] = $mainCategory->getCId();
            $category['categoryName'] = $mainCategory->getCategoryName();
            $category['parentId'] = $mainCategory->getParentId();
            $category['sub_categories'] = $this->getCategoryTreeForParentId($category['cid']);
            $categories[$mainCategory->getCId()] = $category;
        }
        
        return $categories;
    }
    
    public function doAddCategory(Request $request) {
        $postedData = $request->request->all();
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        if($postedData['categoryId'] == '') {
            try{
            $category = new Category();
            
            $category->setParentId($postedData['category']);
            $category->setCategoryName($postedData['subcategory']);

            $entityManager = $this->app['doctrine'];
            $entityManager->persist($category);
            $entityManager->flush();

            $sessionData = $this->app['session'];
            $sessionData->getFlashBag()->add('admin_message','Category added successfully');
            } catch (UniqueConstraintViolationException $ex) {
                return $this->app['twig']->render('admin/catsetting.twig');
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
                return $this->app['twig']->render('admin/catsetting.twig');
            }
        
            return $this->app->redirect('/category');
            }
        
        return $this->app->redirect('/addcategory');  
    }

    public function addCategory() {
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];

        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        return $this->app['twig']->render('admin/addcategory.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories));
    }
    
    public function editCategory($id) {
        $sessionData = $this->app['session'];
        $adminLogInEmail = $sessionData->get('loginAdminEmail');
        
        $entityManager = $this->app['doctrine'];
        $categoryRepository = $entityManager->getRepository('Entity\Category');
        $categories = $categoryRepository->findAll();
        
        $categoryDetails = $entityManager->find('Entity\Category',$id);
        
        return $this->app['twig']->render('admin/addcategory.twig',array('UserEmail' => $adminLogInEmail, 'categories' => $categories, 'categoryDetails' => $categoryDetails));
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

    public function adminLogout() {
       $sessionData = $this->app['session'];
       $sessionData->clear();
       return $this->app->redirect("/admin");
    }
}

