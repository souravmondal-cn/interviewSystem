<?php

namespace Controller;
use Entity\User;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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
        
        return $this->app['twig']->render('admin/qaupload.twig',array('UserEmail' => $adminLogInEmail));
    }
    
    public function doUpload(Request $request) {
        $postedData = $request->request->all();
    }
    
    public function adminLogout() {
       $sessionData = $this->app['session'];
       $sessionData->clear();
       return $this->app->redirect("/admin");
    }
}

