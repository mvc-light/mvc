<?php

namespace MvcLight\Model;

class Core {

    protected static $connection;
    protected static $database;
    protected static $app;
    private $host;
    private $user;
    private $password;
    private $dbname;

    public function init($info, $app) {
        static::$app = $app;
        $this->host = $info['host'];
        $this->user = $info['user'];
        $this->password = $info['password'];
        $this->dbname = $info['database'];
        return $this;
    }

    public static function getInstance() {
        return new static();
    }

    public function connect() {
        if (!static::$connection) {
            static::$connection = mysqli_connect($this->host, $this->user, $this->password);
            self::selectDb();
        }
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
