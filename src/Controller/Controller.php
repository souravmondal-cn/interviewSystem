<?php

namespace Controller;

class Controller {

    /**
     * @var \Silex\Application 
     */
    protected $app;
    public $baseUrl;

    public function __construct($app) {
        $this->app = $app;
    }

}
