<?php

use Controller\HomeController;
use Controller\AdminController;

$app['home.controller'] = $app->share(function() use ($app) {
    return new HomeController($app);
});

$app['admin.controller'] = $app->share(function() use ($app) {
    return new AdminController($app);
});

$app->get("/", 'home.controller:login');
$app->get("/register", 'home.controller:showRegistrationForm');
$app->post("/register", 'home.controller:registerNewUser');
$app->post("/login", 'home.controller:doLogin');
$app->get("/dashboard", 'home.controller:dashboard');
$app->get("/logout", 'home.controller:logout');

$app->get("/admin", 'admin.controller:loginAdmin');
$app->post("/adminlogin", 'admin.controller:doLoginAdmin');
$app->get("/adminpanel", 'admin.controller:adminPanel');
$app->get("/adminlogout",'admin.controller:adminLogout');

$app->get("/questionlisting","admin.controller:questionListing");
$app->get("/questionupload","admin.controller:questionUpload");
$app->post("/questionupload","admin.controller:doUpload");
$app->get("/editQuestion/{id}", "admin.controller:editQuestion");
$app->get("/deleteQuestion/{id}", "admin.controller:deleteQuestion");

$app->get("/category","admin.controller:categorySetting");
$app->get("/addcategory","admin.controller:addCategory");
$app->post("/addcategory","admin.controller:doAddCategory");
$app->get("/editCategory/{id}", "admin.controller:editCategory");
$app->get("/deleteCategory/{id}", "admin.controller:deleteCategory");

$app->get("/adminsetting","admin.controller:showAdminSettings");
$app->get("/usersetting","admin.controller:showUserSettings");
$app->get("/adduser/{userType}", "admin.controller:addAllUsers");
$app->post("/adduser/{userType}", "admin.controller:doAddUser");
$app->get("/edituser/{userType}/{id}", "admin.controller:editUserData");
$app->get("/deleteuser/{userType}/{id}", "admin.controller:deleteUserData");

$app->get("/examsetting","admin.controller:examSetting");
$app->post("/examsetting","admin.controller:examGenerate");

$app->post("/checkEmail/{email}","admin.controller:checkUserRegistration");
$app->get("/viewHistory/{email}", "admin.controller:listExamHistory");
$app->get("/viewfile/{filename}","admin.controller:downloadFile");

$app->get("/examnow/{email}", "home.controller:examNow");
$app->get("/displayQuestion", "home.controller:displayQuestion");
$app->post("/examsubmit", "home.controller:examSubmit");
$app->get("/examsubmit", "home.controller:forceSubmit");