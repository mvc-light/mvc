<?php

namespace MvcLight\Core;

class Router {

    private static $route = array();
    private static $array_pattern = array();
    private static $seleted_route = '';
    private static $seleted_param = array();

    public function __construct() {
        self::set_array_pattern();
    }

    public function get($key) {
        switch ($key) {
            case 'all_route':
                return self::$route;
            case 'seleted_route':
                return array(
                    'name_route' => self::$seleted_route,
                    'param_route' => self::$seleted_param
                );
        }
        return NULL;
    }

    public function getRoute($name) {
        if (isset(self::$route[$name])) {
            return self::$route[$name];
        }
        return NULL;
    }

    public function getUrl($name, $param = array()) {
        $uri_route = $this->getRoute($name)[0];
        if (!$uri_route) {
            return array(
                'state' => FALSE,
                'errorName' => 'Name Route Error!',
                'errorText' => "The Route \"$name\" is not exist!"
            );
        }
        $var_s = self::get_variable_array($uri_route);
        if (count($var_s) > 0) {
            foreach ($var_s as $var) {
                $split = explode(':', $var);
                $name_var = ltrim($split[0], '{');
                if (!isset($param[$name_var])) {
                    return array(
                        'state' => FALSE,
                        'errorName' => 'Parameter Route Error!',
                        'errorText' => "The Parameter \"$name_var\" is missing!"
                    );
                }
                $uri_route = str_replace($var, $param[$name_var], $uri_route);
            }
            return $uri_route;
        } else {
            return $uri_route;
        }
    }

    public function getAllParam($uri) {
        var_dump(self::get_variable_array($uri));
    }

    private static function set_array_pattern() {
        self::$array_pattern = array(
            'num' => '\-?[0-9]+',
            'slug' => '[0-9a-zA-z\-\+\_\~]+',
            'all' => '.+'
        );
    }

    public function loadRouteFile($file) {
        if (is_file($file)) {
            self::$route = include $file;
        }
    }

    public function run($path) {
        $check = FALSE;
        $part_path = self::main_split_routing($path, '#[\/]+#');
        $count_path = count($part_path);

        foreach (self::$route as $key => $route_info) {
            $uri = $route_info[0];
            $part_uri = self::main_split_routing($uri, '#[\/]+#');
            $count_uri = count($part_uri);
            if ($path == $uri) {
                self::$seleted_route = $key;
                return TRUE;
            }
            if ($count_path > $count_uri) {
                continue;
            }
            for ($i = 0; $i < $count_uri; $i++) {
                if ($part_path[$i] == $part_uri[$i]) {
                    if (self::last_part($count_uri, $i)) {
                        self::$seleted_route = $key;
                        return TRUE;
                    } else {
                        continue;
                    }
                    
                } else {
                    $check = self::check_part($part_path[$i], $part_uri[$i]);
                    if ($check) {
                        self::$seleted_param = array_merge(self::$seleted_param, $check);
                        if (self::last_part($count_uri, $i)) {
                            self::$seleted_route = $key;
                            return TRUE;
                        } else {
                            continue;
                        }
                    } else {
                        self::$seleted_param = array();
                        break;
                    }
                }
            }
        }
    }

    private static function last_part($count_uri, $current_index) {
        return $count_uri == ($current_index + 1);
    }

    private static function main_split_routing($string) {
        $trim_string = ltrim($string, '/');
        $return = explode('/', $trim_string);
        return $return;
    }

    private static function check_part($path_i, $uri_i) {
        $return = array();
        $param = self::get_variable_array($uri_i);
        if ($param !== NULL) {
            $pattern = $uri_i;
            foreach ($param as $p) {
                $split = explode(':', $p);
                $name = ltrim($split[0], '{');
                $type = rtrim($split[1], '}');
                $return[$name] = '';
                $pattern = str_replace($p, '(' . self::$array_pattern[$type] . ')', $pattern);
            }
            $pattern = '!^' . $pattern . '$!';
            $matches = array();
            $count = preg_match($pattern, $path_i, $matches);
            $x = 1;
            if ($count > 0) {
                foreach ($return as $key => $value) {
                    $return[$key] = $matches[$x];
                    $x++;
                }
                return $return;
            }
            return NULL;
        }
        return NULL;
    }

    private static function get_variable_array($string) {
        $matches = array();
        $count = preg_match_all('#\{[a-zA-Z]{1}[a-zA-Z0-9]{0,}[\:]{1}[a-z]{1,10}\}#', $string, $matches);
        if ($count > 0) {
            return $matches[0];
        }
        return NULL;
    }

}
