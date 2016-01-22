<?php

namespace MvcLight;

use Twig_Loader_Filesystem;
//use Twig_Environment;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class App {

    private $env = '';
    private $twig = NULL;
    private $request = NULL;
    private $route = array();
    private $error = array();

    const DIR_VIEW = ROOTDIR . DS . 'app' . DS . 'view';
    const DIR_CACHE = ROOTDIR . DS . 'app' . DS . 'cache';

    public function __construct($ENV) {
        $this->env = $ENV;
        $this->loadSession();
        $this->loadRoute();
        $this->loadTwig($this->env);
        $this->addFunction();
        $this->addFilter();
    }

    private function loadSession() {
        session_start(session_name('Origin_Session'));
    }

    private function loadRoute() {
        $this->request = new Request();
        $this->route = new Router();
        $this->route->loadRouteFile(ROOTDIR . DS . 'app' . DS . 'config' . DS . 'route.php');
    }

    private function loadTwig($env) {
        $loader = new Twig_Loader_Filesystem(self::DIR_VIEW);
        switch ($env) {
            case 'DEV':
                $this->twig = new TwigCustomize($loader);
                break;
            case 'PRO':
                $this->twig = new TwigCustomize($loader, array(
                    'cache' => self::DIR_CACHE
                ));
                break;
        }
        $this->twig->enableStrictVariables();
        $this->twig->disableAutoReload();
        $this->twig->registerUndefinedFunctionCallback(function ($name) {
            $this->addError('Twig Error Syntax!', "Function: \"" . $name . "\" is not defined!", 404);
            $this->checkError();
        });
//        $this->twig->registerUndefinedVariableCallback(function (){
//            
//        });
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
        $this->error[$name] = array(
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
            $env = $this->env;
            switch ($env) {
                case 'DEV':
                    include ROOTDIR . DS . 'vendor' . DS . 'mvc-light' . DS . 'mvc' . DS . 'MvcLight' . DS . 'error.php';
                    exit;
                case 'PRO':
                    exit;
            }
        }
    }

    private function addFunction() {
        $function = new Twig_SimpleFunction('url', function ($name, $param = array()) {
            return $this->url($name, $param);
        });
        $this->twig->addFunction($function);
    }

    private function addFilter() {
        
    }

}
