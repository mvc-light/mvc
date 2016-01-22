<?php

namespace MvcLight;

use MvcLight\Model\Core;

class Origin {

    private $app = null;
    private $controller;
    private $action;
    private $param;

    const DIR_VIEW = ROOTDIR . DS . 'app' . DS . 'view';

    public function run() {
        self::access();
    }

    public function ENVPRO() {
        $this->app->setENV('PRO');
    }

    public static function getStatic() {
        return new static();
    }

    public function getApp() {
        return $this->app;
    }

    public function init($param) {
        $this->app = new App((isset($param['env']) && $param['env'] == 'PRO') ? $param['env'] : 'DEV');
        $info = include ROOTDIR . DS . 'app' . DS . 'config' . DS . 'db.php';
        Core::getInstance()->init($info, $this->app)->connect();
        return $this;
    }

    private function access() {
        self::checkRoute();
        $result = self::runAction();
        self::runView($result['view'], $result['data']);
    }

    private function runView($view, $data = array()) {
        self::checkView($view);
        $data['app'] = $this->app;
        try {
            echo $this->app->getTwig()->render($view, $data);
        } catch (\Twig_Error $ex) {
            self::addError('Twig File Error!', "Messeger: " . $ex->getMessage(), 404);
            self::checkError();
        }
    }

    private function checkView($view) {
        if (!is_file(self::DIR_VIEW . DS . $view)) {
            self::addError('View error!', "File: \"" . str_replace('/', '\\', self::DIR_VIEW . DS . $view) . "\" is not exist!", 404);
        }
        self::checkError();
    }

    private function runAction() {
        $controller = $this->controller;
        $class = '\\App\\Controller\\' . $controller;
        $reflectionClass = new \ReflectionClass($class);
        if (!class_exists($class) || !$reflectionClass->IsInstantiable()) {
            self::addError('Controller error!', "Class: \"$class\" is not exist or can not initialize!", 404);
        } else {
            $action = $this->action;
            if (!method_exists($class, $action)) {
                self::addError('Action error!', "Method: \"$class::$action\" is not exist!", 404);
            } else {

                return call_user_func_array(
                        array(
                    new $class(),
                    $action
                        ), $this->app->getRoute()->get('seleted_route')['param_route']
                );
            }
        }
        self::checkError();
    }

    private function checkRoute() {
        $_path = $this->app->getRequest()->get('path');
        $check_path = $this->app->getRoute()->run($_path);
        if ($check_path) {
            self::set_value_route($this->app->getRoute());
        } else {
//            header('HTTP/1.0 404 Not Found');
            self::addError('Route error!', 'Can not found a router for your path!', 404);
        }
        self::checkError();
    }

    private function set_value_route($routeObj) {
        $info_route = $routeObj->get('seleted_route');
        $name_route = $info_route['name_route'];
        $param_route = $info_route['param_route'];
        $seleted_route = $routeObj->getRoute($name_route);
        self::checkMethod($seleted_route);
        $ca_string = explode(':', $seleted_route[1]);
        if (!isset($ca_string[0]) || $ca_string[0] == '' || !isset($ca_string[1]) || $ca_string[1] == '') {
            self::addError('Route error!', 'Your route is not invalid same structure!', 404);
            self::checkError();
        }
        $this->controller = $ca_string[0] . 'Controller';
        $this->action = $ca_string[1] . 'Action';
        $this->param = $param_route;
    }

    private function checkMethod($seleted_route) {
        $cur_method = $this->app->getRequest()->get('method');
        $allow_method = (isset($seleted_route[2])) ? $seleted_route[2] : '';
        if ($allow_method !== '' && $allow_method !== $cur_method) {
            self::addError('Route error!', 'Method access is not invalid!', 405);
        }
        self::checkError();
    }

    public function addError($name, $msg, $state) {
        $this->app->addError($name, $msg, $state);
    }

    public function checkError() {
        return $this->app->checkError();
    }

}
