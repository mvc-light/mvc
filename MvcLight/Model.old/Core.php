<?php

namespace MvcLight\Modelold;

class Core {

    protected static $connection;
    protected static $database;
    protected static $app;
    private $enable;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $timezone;
    private $charset;

    public function init($info, $app) {
        static::$app = $app;
        $this->enable = $info['enable'];
        $this->host = $info['host'];
        $this->user = $info['user'];
        $this->password = $info['password'];
        $this->dbname = $info['database'];
        $this->timezone = $info['timezone'];
        $this->charset = $info['charset'];
        return $this;
    }

    public static function getInstance() {
        return new static();
    }

    public function connect() {
        if (!static::$connection && $this->enable) {
            static::$connection = mysqli_connect($this->host, $this->user, $this->password);
            self::selectDb();
            self::setTimeZone($this->timezone);
            self::setCharset($this->timezone);
        }
    }

    private function setCharset($charset) {
        mysqli_set_charset(static::$connection, $charset);
    }

    private function setTimeZone($timezone) {
        date_default_timezone_set($timezone);
    }

    private function selectDb() {
        static::$database = mysqli_select_db(static::$connection, $this->dbname);
    }

    public static function getCon() {
        return static::$connection;
    }

    public static function getApp() {
        return static::$app;
    }

}
