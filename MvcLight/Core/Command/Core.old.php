<?php

namespace MvcLight\Core\Command;

class Core {

    protected $app;
    protected $controller;
    protected $action;
    protected $param;
//    protected $env;
//    protected $twig;
//    protected $request;

//    protected $error;
//    protected $route;
//    const DIR_APP = ROOTDIR . DS . 'app' . DS;
    const DIR_VIEW = ROOTDIR . DS . 'app' . DS . 'view';

//    const DIR_CACHE = ROOTDIR . DS . 'app' . DS . 'cache';

    public function __construct($param = array()) {
        if (!$this->app) {
            $this->app = new App((isset($param['env']) && $param['env'] == 'PRO') ? $param['env'] : 'DEV');
        }
    }

    public function ENVPRO() {
        $this->app->setENV('PRO');
    }

    public function addError($name, $msg) {
        $this->app->addError($name, $msg);
    }

    public function checkError() {
        return $this->app->checkError();
    }
}
