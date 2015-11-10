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
$app->get("/questionupload","admin.controller:questionUpload");
$app->post("/questionupload","admin.controller:doUpload");
$app->get("/category","admin.controller:categorySetting");
$app->get("/addcategory","admin.controller:addCategory");
$app->post("/addcategory","admin.controller:doAddCategory");
$app->get("/editCategory/{id}", "admin.controller:editCategory");
$app->get("/deleteCategory/{id}", "admin.controller:deleteCategory");
