<?php

namespace MvcLight\Modelold;

class Base {

    protected static $where = '';
    protected static $select = '*';
    protected static $orderby = '';
    protected static $limit = '';
    protected $table;

    /**
     * @return Boolean Delete some row on database
     */
    public static function delete() {
        $where = static::$where;
        if ($where == '') {
            Core::getApp()->addError("Model Error!", "Message: ::delete() function missing where clause!", 404);
            Core::getApp()->checkError();
        }
        $instance = new static();
        $table = $instance->getTable();
        $sql = "delete from $table";
        if ($where != 'all') {
            $sql = $sql . " $where";
        }
        $state = mysqli_query(Core::getCon(), $sql);
        $error = mysqli_error(Core::getCon());
        if ($error != '') {
            Core::getApp()->addError("Model Error!", "Message: $error!<br><i><b>Your query:</b> $sql</i>", 404);
            Core::getApp()->checkError();
        }
        static::reset();
        return $state;
    }

    /**
     * @param Array $data Array has key is field on database
     * @return Boolean Update some row on database
     */
    public static function update($data) {
        $where = static::$where;
        if ($where == '') {
            Core::getApp()->addError("Model Error!", "Message: ::update() function missing where clause!", 404);
            Core::getApp()->checkError();
        }
        $instance = new static();
        $table = $instance->getTable();
        $fields = array_keys($data);
        $values = self::real_escape_array($data);
        $sql = "update `$table` set";
        for ($i = 0; $i < count($fields); $i++) {
            $sql = $sql . " `$fields[$i]` = '$values[$i]',";
        }
        $sql = rtrim($sql, ',');
        if ($where != '') {
            $sql = $sql . " $where";
        }
        $state = mysqli_query(Core::getCon(), $sql);
        $error = mysqli_error(Core::getCon());
        if ($error != '') {
            Core::getApp()->addError("Model Error!", "Message: $error!<br><i><b>Your query:</b> $sql</i>", 404);
            Core::getApp()->checkError();
        }
        static::reset();
        return $state;
    }

    /**
     * @param Array $data Array has key is field on database
     * @param Boolean $getId TRUE = return new insert id
     * @return Boolean Or Id
     */
    public static function insert($data, $getId = FALSE) {
        $instance = new static();
        $table = $instance->getTable();
        $fields = "( `" . implode("`, `", array_keys($data)) . "` )";
        $values = "( '" . implode("', '", self::real_escape_array($data)) . "' )";
        $sql = "insert into `$table` $fields values $values;";
        $state = mysqli_query(Core::getCon(), $sql);
        $error = mysqli_error(Core::getCon());
        if ($error != '') {
            Core::getApp()->addError("Model Error!", "Message: $error!<br><i><b>Your query:</b> $sql</i>", 404);
            Core::getApp()->checkError();
        }
        return ($getId) ? mysqli_insert_id(Core::getCon()) : $state;
    }

    /**
     * @return Array Get some row on database
     */
    public static function get() {
        $instance = new static();
        $table = $instance->getTable();
        $select = static::$select;
        $where = static::$where;
        $orderby = static::$orderby;
        $limit = static::$limit;
        $sql = "select $select from `$table` $where $orderby $limit";
        $query = mysqli_query(Core::getCon(), $sql);
        $error = mysqli_error(Core::getCon());
        if ($error != '') {
            Core::getApp()->addError("Model Error!", "Message: $error!<br><i><b>Your query:</b> $sql</i>", 404);
            Core::getApp()->checkError();
        }
        static::reset();
        return static::returnArray($query);
    }

    /**
     * @return Array Get first row on database
     */
    public static function first() {
        $instance = new static();
        $table = $instance->getTable();
        $select = static::$select;
        $where = static::$where;
        $orderby = static::$orderby;
        $sql = "select $select from `$table` $where $orderby limit 1";
        $query = mysqli_query(Core::getCon(), $sql);
        $error = mysqli_error(Core::getCon());
        if ($error != '') {
            Core::getApp()->addError("Model Error!", "Message: $error!<br><i><b>Your query:</b> $sql</i>", 404);
            Core::getApp()->checkError();
        }
        static::reset();
        return static::returnFirst($query);
    }

    /**
     * @return Number Return number of rows
     */
    public static function count() {
        $instance = new static();
        $table = $instance->getTable();
        $select = static::$select;
        $where = static::$where;
        $orderby = static::$orderby;
        $limit = static::$limit;
        $sql = "select $select from `$table` $where $orderby $limit";
        static::reset();
        return mysqli_num_rows(mysqli_query(Core::getCon(), $sql));
    }

    /**
     * @param String $where Where Clause
     * @return \static Set Where Clause
     */
    public static function where($where = "") {
        if (!is_string($where)) {
            static::alertErrorString('where', debug_backtrace()[0]);
        }
        static::$where = ($where != "") ? "where " . $where : "";
        return new static();
    }

    /**
     * @param String $select Select Clause
     * @return \static Set Select Clause
     */
    public static function select($select = '*') {
        static::$select = $select;
        return new static();
    }

    /**
     * @param String $orderBy OrderBy Clause
     * @return \static Set OrderBy Clause
     */
    public static function orderBy($orderBy = "") {
        if (!is_string($orderBy)) {
            static::alertErrorString('orderBy', debug_backtrace()[0]);
        }
        static::$orderby = ($orderBy != "") ? "order by " . $orderBy : "";
        return new static();
    }

    /**
     * @param String $limit Limit Clause
     * @return \static Set Limit Clause
     */
    public static function limit($limit = "") {
        if (!is_string($limit)) {
            static::alertErrorString('limit', debug_backtrace()[0]);
        }
        static::$limit = ($limit != "") ? "limit " . $limit : "";
        return new static();
    }

    private static function alertErrorString($action, $info) {
        Core::getApp()->addError("Model Error!", "Message: Parameter of <b>$action()</b> method must has type of String!<br>"
                . "<i><b>" . $info['file'] . "</b>" . " on line " . $info['line'] . "</i>", 404);
        Core::getApp()->checkError();
    }

    private static function reset() {
        static::$select = '*';
        static::$where = '';
        static::$orderby = '';
        static::$limit = '';
    }

    private function getTable() {
        return $this->table;
    }

    private static function returnFirst($result) {
        return mysqli_fetch_assoc($result);
    }

    private static function returnArray($result) {
        $return = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $return[] = $row;
        }
        return $return;
    }

    private static function real_escape_array($data) {
        $return = array();
        foreach ($data as $value) {
            array_push($return, mysqli_real_escape_string(Core::getCon(), $value));
        }
        return $return;
    }

}
