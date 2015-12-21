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

$app->get("/", 'frontendHome.controller:home');

$app->get("/login", 'user.controller:getLoginFrom');
$app->get("/admin/login", 'admin.controller:getAdminLoginForm');

$app->post("/login", 'frontendHome.controller:doLogin');

$app->get("/register", 'user.controller:getRegistrationForm');
$app->post("/register", 'user.controller:registerUser');

$app->get("/logout", 'user.controller:logout');
$app->get("/dashboard", 'user.controller:dashboard');

$app->get("/admin", 'admin.controller:adminPanel');
$app->get("/adminlogout",'admin.controller:adminLogout');

$app->get("/questionlisting","admin.settings:questionListing");
$app->get("/questionupload","admin.settings:questionUpload");
$app->post("/questionupload","admin.settings:doUpload");
$app->get("/editQuestion/{id}", "admin.settings:editQuestion");
$app->get("/deleteQuestion/{id}", "admin.settings:deleteQuestion");

$app->get("/category","admin.settings:categorySetting");
$app->get("/addcategory","admin.settings:addCategory");
$app->post("/addcategory","admin.settings:doAddCategory");
$app->get("/editCategory/{id}", "admin.settings:editCategory");
$app->get("/deleteCategory/{id}", "admin.settings:deleteCategory");

$app->get("/adminsetting","admin.controller:showAdminSettings");
$app->get("/usersetting","admin.controller:showUserSettings");
$app->get("/adduser/{userType}", "admin.controller:addAllUsers");
$app->post("/adduser/{userType}", "admin.controller:doAddUser");
$app->get("/edituser/{userType}/{id}", "admin.controller:editUserData");
$app->get("/deleteuser/{userType}/{id}", "admin.controller:deleteUserData");

$app->post("/checkEmail/{email}","admin.controller:checkUserRegistration");
$app->get("/viewfile/{filename}","admin.controller:downloadFile");

$app->get("/examsetting","exam.settings:examSetting");
$app->post("/examsetting","exam.settings:examGenerate");

$app->get("/viewHistory/{userId}", "exam.settings:listExamHistory");
$app->get("/examdetail/{examId}", "exam.settings:viewExamDetail");
$app->get("/setQualified/{examId}", "exam.settings:setQualified");

$app->get("/examstart/{userId}", "exam.controller:examStart");
$app->get("/displayQuestion", "exam.controller:displayQuestion");
$app->post("/examsubmit", "exam.controller:examSubmit");
$app->get("/examsubmit", "exam.controller:forceSubmit");