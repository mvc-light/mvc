<?php

namespace MvcLight\Core;

class Origin {// extends Core {

    private $app = null;
    private $controller;
    private $action;
    private $param;
    private static $static = null;

//    const DIR_APP = ROOTDIR . DS . 'app' . DS;
    const DIR_VIEW = ROOTDIR . DS . 'app' . DS . 'view';

//    const DIR_CACHE = ROOTDIR . DS . 'app' . DS . 'cache';

    public function __construct($param = array()) {
//        parent::__construct($param);
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

    public static function getStatic() {
        if (static::$static === null) {
            static::$static = new static();
        }
        return static::$static;
    }

    public function getApp() {
        return $this->app;
    }

    public function init($param) {
        if (!$this->app) {
            $this->app = new App((isset($param['env']) && $param['env'] == 'PRO') ? $param['env'] : 'DEV');
        }
        return $this;
    }

    public function run() {
        $this->access();
    }

    private function access() {
        $this->checkRoute();
        $result = $this->runAction();
        $this->runView($result['view'], $result['data']);
    }

    private function runView($view, $data = array()) {
        $this->checkView($view);
        $data['app'] = $this->app;
        echo $this->app->getTwig()->render($view, $data);
    }

    private function checkView($view) {
        if (!is_file(self::DIR_VIEW . DS . $view)) {
            $this->addError('View error!', "File: \"$view\" is not exist!");
        }
        $this->checkError();
    }

    private function runAction() {
        $controller = $this->controller;
        $class = '\\App\\Controller\\' . $controller;
        if (!class_exists($class)) {
            $this->addError('Controller error!', "Class: \"$controller\" is not exist!");
        } else {
            $action = $this->action;
            if (!method_exists($class, $action)) {
                $this->addError('Action error!', "Method: \"$controller\\$action\" is not exist!");
            } else {
                $CTRL = new $class();
                return call_user_func_array(array($CTRL, $action), $this->app->getRoute()->get('seleted_route')['param_route']);
            }
        }
        $this->checkError();
    }

    private function checkRoute() {
//        $this->route = new Router();
//        $this->route->loadRouteFile(ROOTDIR . DS . 'app' . DS . 'config' . DS . 'route.php');
        $_path = $this->app->getRequest()->get('path');
        $check_path = $this->app->getRoute()->run($_path);
        if ($check_path) {
            self::set_value_route($this->app->getRoute());
        } else {
            header('HTTP/1.0 404 Not Found');
            $this->addError('Route error!', 'Can not found a router for your path!');
        }
        $this->checkError();
    }

    private function set_value_route($routeObj) {
        $info_route = $routeObj->get('seleted_route');
        $name_route = $info_route['name_route'];
        $param_route = $info_route['param_route'];
        $seleted_route = $routeObj->getRoute($name_route);
        $ca_string = explode(':', $seleted_route[1]);
        $this->controller = $ca_string[0];
        $this->action = $ca_string[1] . 'Action';
        $this->param = $param_route;
    }

}
