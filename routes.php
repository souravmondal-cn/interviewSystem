<?php

use Controller\FrontEnd\Home;
use Controller\UserController;
use Controller\Admin\AdminController;
use Controller\Admin\AdminSettingsController;
use Controller\Admin\ExamSettingsController;
use Controller\FrontEnd\ExamController;

$app['frontendHome.controller'] = $app->share(function() use ($app) {
    return new Home($app);
});
$app['user.controller'] = $app->share(function() use ($app) {
    return new UserController($app);
});

$app['admin.controller'] = $app->share(function() use ($app) {
    return new AdminController($app);
});

$app['admin.settings'] = $app->share(function () use ($app) {
    return new AdminSettingsController($app);
});

$app['exam.settings'] = $app->share(function () use ($app) {
    return new ExamSettingsController($app);
});

$app['exam.controller'] = $app->share(function () use ($app) {
    return new ExamController($app);
});

$app->get(BASEPATH."/", 'frontendHome.controller:home');

$app->get(BASEPATH."/login", 'user.controller:getLoginFrom');
$app->get(BASEPATH."/admin/login", 'admin.controller:getAdminLoginForm');

$app->post(BASEPATH."/login", 'frontendHome.controller:doLogin');

$app->get(BASEPATH."/register", 'user.controller:getRegistrationForm');
$app->post(BASEPATH."/register", 'user.controller:registerUser');

$app->get(BASEPATH."/logout", 'user.controller:logout');
$app->get(BASEPATH."/dashboard", 'user.controller:dashboard');

$app->get(BASEPATH."/admin", 'admin.controller:adminPanel');
$app->get(BASEPATH."/adminlogout",'admin.controller:adminLogout');

$app->get(BASEPATH."/questionlisting","admin.settings:questionListing");
$app->get(BASEPATH."/questionupload","admin.settings:questionUpload");
$app->post(BASEPATH."/questionupload","admin.settings:doUpload");
$app->get(BASEPATH."/editQuestion/{id}", "admin.settings:editQuestion");
$app->get(BASEPATH."/deleteQuestion/{id}", "admin.settings:deleteQuestion");

$app->get(BASEPATH."/category","admin.settings:categorySetting");
$app->get(BASEPATH."/addcategory","admin.settings:addCategory");
$app->post(BASEPATH."/addcategory","admin.settings:doAddCategory");
$app->get(BASEPATH."/editCategory/{id}", "admin.settings:editCategory");
$app->get(BASEPATH."/deleteCategory/{id}", "admin.settings:deleteCategory");

$app->get(BASEPATH."/adminsetting","admin.controller:showAdminSettings");
$app->get(BASEPATH."/usersetting","admin.controller:showUserSettings");
$app->get(BASEPATH."/adduser/{userType}", "admin.controller:getAddUserForm");
$app->post(BASEPATH."/adduser/{userType}", "admin.controller:doAddUser");
$app->get(BASEPATH."/edituser/{userType}/{id}", "admin.controller:editUserData");
$app->get(BASEPATH."/deleteuser/{userType}/{id}", "admin.controller:deleteUserData");

$app->post(BASEPATH."/checkEmail/{email}","admin.controller:checkUserRegistration");
$app->get(BASEPATH."/viewfile/{filename}","admin.controller:downloadFile");

$app->get(BASEPATH."/examsetting","exam.settings:examSetting");
$app->post(BASEPATH."/examsetting","exam.settings:examGenerate");

$app->get(BASEPATH."/viewHistory/{emailId}", "exam.settings:listExamHistory");
$app->get(BASEPATH."/examdetail/{examId}", "exam.settings:viewExamDetail");
$app->get(BASEPATH."/setQualified/{examId}", "exam.settings:setQualified");

$app->get(BASEPATH."/examstart/{userId}", "exam.controller:examStart");
$app->get(BASEPATH."/displayQuestion", "exam.controller:displayQuestion");
$app->post(BASEPATH."/examsubmit", "exam.controller:examSubmit");
$app->get(BASEPATH."/examsubmit", "exam.controller:forceSubmit");