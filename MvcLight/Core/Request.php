<?php

namespace MvcLight\Core;

class Request {
    
    private $server;
    private $path;
    private $port;
    private $scheme;
    private $method;
    private $url;
    private $requestTime;
    private $queryString;

    public function __construct() {
        $this->server = $_SERVER['SERVER_NAME'];
        
        
        $path_current = $_SERVER['REQUEST_URI'];
        if (strpos($path_current, '?')) {
            $path_current = substr($path_current, 0, strpos($path_current, '?'));
        }
        $this->path = $path_current;
        
        
        $this->port = $_SERVER['SERVER_PORT'];
        $this->scheme = $_SERVER['REQUEST_SCHEME'];
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $this->requestTime = $_SERVER['REQUEST_TIME'];
        $this->queryString = $_SERVER['QUERY_STRING'];
    }
    
    public function get($key){
        switch ($key){
            case 'server':
                return $this->server;
            case 'path':
                return $this->path;
            case 'port':
                return $this->port;
            case 'scheme':
                return $this->scheme;
            case 'method':
                return $this->method;
            case 'url':
                return $this->url;
            case 'requestTime':
                return $this->requestTime;
            case 'queryString':
                return $this->queryString;
        }
    }
    
}
