<?php

namespace MvcLight\Model;

use PDO;

class Base {

    private static $pdo;
    protected $table;
    protected $attributes = array();
    protected static $app;

    public function __get($name) {
//        if (isset($this->$name) && $name != 'extra_column_model') {
//            return $this->$name;
//        }
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return NULL;
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __call($name, $ags) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return NULL;
    }

    public function init($info, $app) {
        self::$app = $app;
        $driver = $info['driver'];
        $dbname = $info['database'];
        $host = $info['host'];
        $user = $info['user'];
        $pass = $info['password'];
        $encoding = $info['charset'];
        $timezone = $info['timezone'];
        $option = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $encoding"
        );
        self::$pdo = new PDO("$driver:dbname=$dbname;host=$host", $user, $pass, $option);
        date_default_timezone_set($timezone);
    }

    public static function all() {
        $table = self::getTB();
        $query = self::$pdo->prepare("SELECT * FROM `$table`");
        try {
            $query->execute();
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        return $query->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public static function query($query) {
        return self::$pdo->prepare($query);
    }

    public static function get($query, $param = array()) {
        try {
            $query->execute($param);
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        return $query->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public static function first($query, $param = array()) {
        try {
            $query->execute($param);
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        return $query->fetchObject(get_called_class());
    }

    public static function insert($data) {
        $table = self::getTB();
        $fields = implode(',', array_keys($data));
        $value = implode(',:', array_keys($data));
        $sql = "INSERT INTO `$table` ($fields) VALUES (:$value)";
        $query = self::$pdo->prepare($sql);
        try {
            $query->execute($data);
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        $lastId = self::$pdo->lastInsertId();
        return $lastId;
    }

    public static function update($data, $where = FALSE) {
        $table = self::getTB();
        $fields = array_keys($data);
        $count = count($fields);
        $sql = "UPDATE `$table` SET";
        foreach ($fields as $key => $field) {
            $value = self::$pdo->quote($data[$field]);
            $sql .= " `$field` = $value";
            if ($key < ($count - 1)) {
                $sql .= ',';
            }
        }
        if ($where === FALSE) {
            return FALSE;
        }
        if ($where !== '') {
            $sql .= " WHERE ($where)";
        }
        try {
            $result = self::$pdo->exec($sql);
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        return $result;
    }

    public static function delete($where = FALSE) {
        $table = self::getTB();
        $sql = "DELETE FROM $table";
        if ($where === FALSE || $where === '') {
            return FALSE;
        }
        if ($where !== TRUE) {
            $sql .= " WHERE ($where)";
        }
        try {
            $result = self::$pdo->exec($sql);
        } catch (PDOException $ex) {
            self::checkError($ex);
        }
        return $result;
    }

    public static function count($query) {
        return $query->rowCount();
    }

    private static function getTB() {
        $instance = new static();
        return $instance->table;
    }

    private static function checkError($errors) {
        var_dump($errors);
        die;
        //file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
    }

    public static function getInstance() {
        return new static();
    }

}
