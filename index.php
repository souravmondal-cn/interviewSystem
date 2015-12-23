<?php

require_once './bootstrap.php';

use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

use Controller\Application;

$app = new Application();

$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

$app->register(new SessionServiceProvider());
$app['session.storage.handler'] = null;

$app['debug'] = true;

/* @var $entityManager \Doctrine\ORM\EntityManager */

$app['doctrine'] = $entityManager;
$app['basedir'] = BASEPATH;
require_once './routes.php';

$app->run();