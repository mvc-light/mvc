<?php

namespace MvcLight\Controller;

use MvcLight\Origin;

class Base {

    protected $app = NULL;

    public function __construct() {
        $this->app = Origin::getStatic()->getApp();
    }

    public function render($view, $data = array()) {
        return array(
            'view' => $view,
            'data' => $data
        );
    }
    
    public function getSS($name){
        return $this->app->getSS($name);
    }
    
    public function setSS($name, $value){
        return $this->app->setSS($name, $value);
    }
    
    public function redirect($name, $param = array()){
        return $this->app->redirect($this->app->url($name, $param));
    }

}
