<?php

namespace MvcLight\Core;

use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use MvcLight\Twig\TwigCustomize;
use MvcLight\Origin;

class App {

    private $env = '';
    private $twig = NULL;
    private $request = NULL;
    private $route = array();
    private $error = array();
    private $redirectError = array();

    public function __construct($ENV) {
        $this->env = $ENV;
        $this->loadSession();
        $this->loadRoute();
        $this->loadRedirectError();
        $this->loadTwig($this->env);



        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            if ($this->env == 'PRO') {
                return false;
            }
            $this->addError('Fatal Error!', $errstr . "<br><i><b/>$errfile </b> on line $errline</i>", 404);
            $this->checkError();
        });
    }

    private function loadRedirectError() {
        $this->redirectError = include ROOTDIR . DS . 'application' . DS . 'config' . DS . 'error.php';
    }

    private function loadSession() {
        session_start(session_name('Origin_Session'));
    }

    private function loadRoute() {
        $this->request = new Request();
        $this->route = new Router();
        $this->route->loadRouteFile(ROOTDIR . DS . 'application' . DS . 'config' . DS . 'route.php');
    }

    private function loadTwig($env) {
        $dir_view = ROOTDIR . DS . 'application' . DS . 'view';
        $dir_cache = ROOTDIR . DS . 'application' . DS . 'cache';
        $twig_inport = include ROOTDIR . DS . 'application' . DS . 'config' . DS . 'twig.php';
        $loader = new Twig_Loader_Filesystem($dir_view);
        switch ($env) {
            case 'DEV':
                $this->twig = new TwigCustomize($loader);
                break;
            case 'PRO':
                $this->twig = new TwigCustomize($loader, array(
                    'cache' => $dir_cache
                ));
                break;
        }
        $this->twig->enableStrictVariables();
        $this->twig->disableAutoReload();
//        $this->twig->registerUndefinedFunctionCallback(function ($name) {
//            $this->addError('Twig Error Syntax!', "Function: \"" . $name . "\" is not defined!", 404);
//            $this->checkError();
//        });
        $this->loadDefalt();
        $this->addFunction($twig_inport['function']);
        $this->addFilter($twig_inport['filter']);
    }

    public function redirect($url) {
        header('Location: ' . $url);
    }

    public function setSS($name, $value = NULL) {
        $_SESSION[$name] = $value;
    }

    public function getSS($name) {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return NULL;
    }

    public function getRequest() {
        return $this->request;
    }

    public function getTwig() {
        return $this->twig;
    }

    public function getRoute() {
        return $this->route;
    }

    public function url($name, $param = array()) {
        $result = $this->route->getUrl($name, $param);
        if (is_array($result)) {
            $this->addError($result['errorName'], $result['errorText']);
            $this->checkError();
        }
        $uri = $this->request->get('scheme') . '://' . $this->request->get('server') . $result;
        return $uri;
    }

    public function addError($name, $msg, $state) {
        $this->error[] = array(
            'name' => $name,
            'msg' => $msg,
            'state' => $state
        );
    }

    public function checkError() {
        $error = $this->error;
        if (count($error) == 0) {
            return TRUE;
        } else {
            ob_get_clean();
            $state = $error[0]['state'];
            static::headerError($state);
            $env = $this->env;
            switch ($env) {
                case 'DEV':
                    include ROOTDIR . DS . 'vendor' . DS . 'mvc-light' . DS . 'mvc' . DS . 'MvcLight' . DS . 'Error' . DS . 'error.php';
                    exit;
                case 'PRO':
                    $this->redirectErrorRoute($state);
                    exit;
            }
        }
    }

    private function redirectErrorRoute($state) {
        $name_route = $this->getItem($state, $this->redirectError);
        if (!$name_route) {
            $this->errorThrowRedirect();
        }
        $this->redirect($this->url($name_route));
//        $route = $this->route->getRoute($name_route);
//        if (!$route) {
//            $this->errorThrowRedirect();
//        }
//        $CA = array_filter(explode(':', $route[1]));
//        if (count($CA) != 2) {
//            $this->errorThrowRedirect();
//        }
//        $ctrl = $CA[0] . 'Controller';
//        $act = $CA[1] . 'Action';
//        $class = '\\App\\Controller\\' . $ctrl;
//        if (!class_exists($class)) {
//            $this->errorThrowRedirect();
//        }
//        $reflectionClass = new \ReflectionClass($class);
//        if (!$reflectionClass->IsInstantiable()) {
//            $this->errorThrowRedirect();
//        }
//        if (!method_exists($class, $act)) {
//            $this->errorThrowRedirect();
//        }
//        $result = call_user_func_array(array(new $class(), $act), array());
//        if (!$result) {
//            $this->errorThrowRedirect();
//        }
//        $dir_view = ROOTDIR . DS . 'application' . DS . 'view';
//        $view = $result['view'];
//        if (!is_file($dir_view . DS . $view)) {
//            $this->errorThrowRedirect();
//        }
//        $data = $result['data'];
//        $data['app'] = $this;
//        try {
//            echo $this->twig->render($view, $data);
//        } catch (\Twig_Error $ex) {
//            $this->errorThrowRedirect();
//        }
//        die;
    }

    public function runAction($ctrl, $act) {
        $controller = $ctrl;
        $class = '\\App\\Controller\\' . $controller;
        if (!class_exists($class)) {
            $this->addError('Controller error!', "Class: <b>\"$class\"</b> is not exist!", 500);
            $this->checkError();
        } else {
            $this->checkInstantiable($class);
            $action = $act;
            if (!method_exists($class, $action)) {
                $this->addError('Action error!', "Method: <b>\"$class::$action\"</b> is not exist!", 500);
                $this->checkError();
            } else {
                return call_user_func_array(
                        array(
                    new $class(),
                    $action
                        ), $this->route->get('seleted_route')['param_route']
                );
            }
        }
    }

    private function checkInstantiable($class) {
        $reflectionClass = new \ReflectionClass($class);
        if (!$reflectionClass->IsInstantiable()) {
            $this->addError('Controller error!', "Class: <b>\"$class\"</b> is can not initialize!", 404);
            $this->checkError();
        }
    }

    private function errorThrowRedirect() {
        echo "<b style='font-size: 36px; margin-top: 20px; display: block;'>Page not found!</b><br><hr>";
        echo "<span>Server can not access this request!<span/>";
        exit;
    }

    private function getItem($name, $array = array()) {
        return (isset($array[$name])) ? $array[$name] : false;
    }

    private static function headerError($numberError) {
        switch ($numberError) {
            case 404:
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found!');
                break;
            case 405:
                header($_SERVER['SERVER_PROTOCOL'] . ' 405 Not Allow Method!');
                break;
            case 500:
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
                break;
        }
    }

    private function addFunction($list) {
        $function = new Twig_SimpleFunction('url', function ($name, $param = array()) {
            return $this->url($name, $param);
        });
        $this->twig->addFunction($function);
        foreach ($list as $name => $func) {
            $new_func = new Twig_SimpleFunction($name, $func);
            $this->twig->addFunction($new_func);
        }
    }

    private function loadDefalt() {
        $default_func = include __DIR__ . DS . '..' . DS . 'Twig' . DS . 'functionDefault.php';
        foreach ($default_func as $name => $func) {
            $new_func = new Twig_SimpleFunction($name, $func);
            $this->twig->addFunction($new_func);
        }
        $default_filter = include __DIR__ . DS . '..' . DS . 'Twig' . DS . 'filterDefault.php';
        foreach ($default_filter as $name => $filter) {
            $new_filter = new Twig_SimpleFilter($name, $filter);
            $this->twig->addFilter($new_filter);
        }
    }

    private function addFilter($list) {
        foreach ($list as $name => $filter) {
            $new_filter = new Twig_SimpleFilter($name, $filter);
            $this->twig->addFilter($new_filter);
        }
    }

}
