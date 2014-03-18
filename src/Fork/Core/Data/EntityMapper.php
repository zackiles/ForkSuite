<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Data;

use Fork\Fork;

/**
 * An abstract class used to map derived classed into database entities which then can be dynamically
 * inserted, updated, and deleted in an object oriented way.
 *
 * @author Jerome Marshall <jeromemarshall@boxedresearch.com>
 */
abstract class EntityMapper
{

    // ** OVERRIDE THE FOLLOWING as appropriate in your sub-class
    protected static $_primary_column_name = 'id'; // primary key column
    protected static $_tableName = null;           // database table name


    public static $_db;  // all models inherit this db connection
    protected static $_stmt = array(); // prepared statements cache
    protected static $_identifier_quote_character = null;  // character used to quote table & columns names
    private static $_tableColumns = array();             // columns in database table populated dynamically

    function __construct(array $data = array()) {

        static::getFieldnames();  // only called once first time an object is created
        if (is_array($data)) {
            $this->populateData($data);
       }

    }

    public static function isDatabaseConnected(){
        if (!self::$_db){
            // refactor this later obviously. needs proper injection for unit testing etc.
            self::$_db = Fork::Database();
            self::$_identifier_quote_character = MySqlPDO::getDriverQuoteCharacter(self::$_db);
        };
        return true;
    }

     /**
     * Quote a string that is used as an identifier
     * (table names, column names etc). This method can
     * also deal with dot-separated identifiers eg table.column
     *
     * @param string $identifier
     * @return string
     */
    protected static function _quote_identifier($identifier) {
        $class = get_called_class();
        $parts = explode('.', $identifier);
        $parts = array_map(array($class, '_quote_identifier_part'), $parts);
        return join('.', $parts);
    }


    /**
     * This method performs the actual quoting of a single
     * part of an identifier, using the identifier quote
     * character specified in the config (or autodetected).
     *
     * @param string $part
     * @return string
     */
    protected static function _quote_identifier_part($part) {
        if ($part === '*') {
            return $part;
        }
        return static::$_identifier_quote_character . $part . static::$_identifier_quote_character;
    }

    /**
     * Get and cache on first call the column names assocaited with the current table
     *
     * @return array of column names for the current table
     */
    protected static function getFieldnames() {
        $class = get_called_class();
        if (!isset(self::$_tableColumns[$class])) {
            $st = static::execute('DESCRIBE ' . static::_quote_identifier(static::$_tableName));
            self::$_tableColumns[$class] = $st->fetchAll(\PDO::FETCH_COLUMN);
        }
        return self::$_tableColumns[$class];
    }

    /**
     * Given an associative array of key value pairs
     * set the corresponding member value if associated with a table column
     * ignore keys which dont match a table column name
     *
     * @param associative array $data
     * @return void
     */
    public function populateData($data) {
        foreach(static::getFieldnames() as $fieldname) {
            if (isset($data[$fieldname])) {
                $this->$fieldname = $data[$fieldname];
            } else if (!isset($this->$fieldname)) { // PDO pre populates fields before calling the constructor, so dont null unless not set
                $this->$fieldname = null;
            }
        }
    }

    /**
     * Given an associative array of key value pairs
     * set the corresponding member value if associated with a table column
     * ignore keys which dont match a table column name
     *
     * @param associative array $data
     * @return object
     */
    public static function mapEntityFromArray(array $data, $classInstance = null){
        $callingClass = get_called_class();
        if (! $classInstance){
            $classInstance = new $callingClass();
        }
        foreach ($classInstance as $prop => $value)
        {
            if (array_key_exists($prop,$data)){
                $classInstance->$prop = $data[$prop];
            }
        }
        return $classInstance;
    }

    /**
     * set all members to null that are associated with table columns
     *
     * @return void
     */
    public function clear() {
        foreach(static::getFieldnames() as $fieldname) {
            $this->$fieldname = null;
        }
    }

    public function __sleep() {
        return static::getFieldnames();
    }

    public function toArray() {
        $a = array();
        foreach(static::getFieldnames() as $fieldname) {
            $a[$fieldname] = $this->$fieldname;
        }
        return $a;
    }

    /**
     * Get the record with the matching primary key
     *
     * @param string $id
     * @return $this::Object
     */
    static public function getById($id) {
        return static::fetchOneWhere(static::_quote_identifier(static::$_primary_column_name).' = ?',array($id));
    }

    /**
     * Get the first record in the table
     *
     * @return $this::Object
     */
    static public function first() {
        return static::fetchOneWhere('1=1 ORDER BY '.static::_quote_identifier(static::$_primary_column_name).' ASC');
    }

    /**
     * Get the last record in the table
     *
     * @return $this::Object
     */
    static public function last() {
        return static::fetchOneWhere('1=1 ORDER BY '.static::_quote_identifier(static::$_primary_column_name).' DESC');
    }

    /**
     * Find records with the matching primary key
     *
     * @param string $id
     * @return array of objects for matching records
     */
    static public function find($id) {
        $find_by_method = 'find_by_'.(static::$_primary_column_name);
        static::$find_by_method($id);
    }

    /**
     * handles calls to non-existant static methods, used to implement dynamic finder and counters ie.
     *  find_by_name('tom')
     *  find_by_title('a great book')
     *  count_by_name('tom')
     *  count_by_title('a great book')
     *
     * @param string $name
     * @param string $arguments
     * @return same as ::fetchAllWhere() or ::countAllWhere()
     */
    static public function __callStatic($name, $arguments) {
        // Note: value of $name is case sensitive.
        if (preg_match('/^find_by_/',$name) == 1) {
            // it's a find_by_{fieldname} dynamic method
            $fieldname = substr($name,8); // remove find by
            $match = $arguments[0];
            if (is_array($match)) {
                return static::fetchAllWhere(static::_quote_identifier($fieldname). ' IN ('.static::createInClausePlaceholders($match).')', $match);
            } else {
                return static::fetchAllWhere(static::_quote_identifier($fieldname). ' = ?', array($match));
            }
        } else if (preg_match('/^findOne_by_/',$name) == 1) {
            // it's a findOne_by_{fieldname} dynamic method
            $fieldname = substr($name,11); // remove findOne_by_
            $match = $arguments[0];
            if (is_array($match)) {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' IN ('.static::createInClausePlaceholders($match).')', $match);
            } else {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' = ?', array($match));
            }
        } else if (preg_match('/^first_by_/',$name) == 1) {
            // it's a first_by_{fieldname} dynamic method
            $fieldname = substr($name,9); // remove first_by_
            $match = $arguments[0];
            if (is_array($match)) {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' IN ('.static::createInClausePlaceholders($match).') ORDER BY ' . static::_quote_identifier($fieldname). ' ASC', $match);
            } else {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' = ? ORDER BY ' . static::_quote_identifier($fieldname). ' ASC', array($match));
            }
        } else if (preg_match('/^last_by_/',$name) == 1) {
            // it's a last_by_{fieldname} dynamic method
            $fieldname = substr($name,8); // remove last_by_
            $match = $arguments[0];
            if (is_array($match)) {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' IN ('.static::createInClausePlaceholders($match).') ORDER BY ' . static::_quote_identifier($fieldname). ' DESC', $match);
            } else {
                return static::fetchOneWhere(static::_quote_identifier($fieldname). ' = ? ORDER BY ' . static::_quote_identifier($fieldname). ' DESC', array($match));
            }
        } else if (preg_match('/^count_by_/',$name) == 1) {
            // it's a count_by_{fieldname} dynamic method
            $fieldname = substr($name,9); // remove find by
            $match = $arguments[0];
            if (is_array($match)) {
                return static::countAllWhere(static::_quote_identifier($fieldname). ' IN ('.static::createInClausePlaceholders($match).')', $match);
            } else {
                return static::countAllWhere(static::_quote_identifier($fieldname). ' = ?', array($match));
            }
        }
        throw new \Exception(__CLASS__.' not such static method['.$name.']');
    }

    /**
     * for a given array of params to be passed to an IN clause return a string placeholder
     *
     * @param array $params
     * @return string
     */
    static public function createInClausePlaceholders($params) {
        return implode(',', array_fill(0, count($params), '?'));  // ie. returns ? [, ?]...
    }

    /**
     * returns number of rows in the table
     *
     * @return int
     */
    static public function count() {
        $st = static::execute('SELECT COUNT(*) FROM '.static::_quote_identifier(static::$_tableName));
        return $st->fetchColumn();
    }

    /**
     * run a SELECT count(*) FROM WHERE ...
     * returns an integer count of matching rows
     *
     * @param string $SQLfragment conditions, grouping to apply (to right of WHERE keyword)
     * @param array $params optional params to be escaped and injected into the SQL query (standrd PDO syntax)
     * @return integer count of rows matching conditions
     */
    static public function countAllWhere($SQLfragment='',$params = array()) {
        if ($SQLfragment) {
            $SQLfragment = ' WHERE '.$SQLfragment;
        }
        $st = static::execute('SELECT COUNT(*) FROM '.static::_quote_identifier(static::$_tableName).$SQLfragment,$params);
        return $st->fetchColumn();
    }

    /**
     * run a SELECT * FROM WHERE ...
     * returns an array of objects of the sub-class
     *
     * @param string $SQLfragment conditions, sorting, grouping and limit to apply (to right of WHERE keywords)
     * @param array $params optional params to be escaped and injected into the SQL query (standrd PDO syntax)
     * @return array of objects of calling class
     */
    static public function fetchAllWhere($SQLfragment='',$params = array()) {
        $class = get_called_class();
        if ($SQLfragment) {
            $SQLfragment = ' WHERE '.$SQLfragment;
        }
        $st = static::execute('SELECT * FROM '.static::_quote_identifier(static::$_tableName).$SQLfragment,$params);
        // $st->debugDumpParams();
        $st->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $st->fetchAll();
    }

    /**
     * run a SELECT * FROM WHERE ... LIMIT 1
     * returns an object of the sub-class
     *
     * @param string $SQLfragment conditions, sorting, grouping and limit to apply (to right of WHERE keywords)
     * @param array $params optional params to be escaped and injected into the SQL query (standrd PDO syntax)
     * @return $this::Object
     */
    static public function fetchOneWhere($SQLfragment='',$params = array()) {
        $class = get_called_class();
        if ($SQLfragment) {
            $SQLfragment = ' WHERE '.$SQLfragment;
        }
        $st = static::execute('SELECT * FROM '.static::_quote_identifier(static::$_tableName).$SQLfragment.' LIMIT 1',$params);
        $st->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $st->fetch();
    }

    /**
     * Delete a record by its primary key
     *
     * @return boolean indicating success
     */
    static public function deleteById($id) {
        $st = static::execute(
            'DELETE FROM '.static::_quote_identifier(static::$_tableName).' WHERE '.static::_quote_identifier(static::$_primary_column_name).' = ? LIMIT 1',
            array($id)
        );
        return ($st->rowCount() == 1);
    }

    /**
     * Delete the current record
     *
     * @return boolean indicating success
     */
    public function delete() {
        return self::deleteById($this->{static::$_primary_column_name});
    }

    /**
     * Delete records based on an SQL conditions
     *
     * @param string $where SQL fragment of conditions
     * @param array $params optional params to be escaped and injected into the SQL query (standrd PDO syntax)
     * @return PDO statement handle
     */
    static public function deleteAllWhere($where,$params = array()) {
        $st = static::execute(
            'DELETE FROM '.static::_quote_identifier(static::$_tableName).' WHERE '.$where,
            $params
        );
        return $st;
    }

    /**
     * do any validation in this function called before update and insert
     * should throw errors on validation failure.
     *
     * @return boolean true or throws exception on error
     */
    static public function validate() {
        return true;
    }

    /**
     * insert a row into the database table, and update the primary key field with the one generated on insert
     *
     * @param boolean $autoTimestamp true by default will set updated_at & created_at fields if present
     * @param string $allowSetPrimaryKey, if true include primary key field in insert (ie. you want to set it yourself)
     * @return boolean indicating success
     */
    public function insert($autoTimestamp = true,$allowSetPrimaryKey = false) {
        $pk = static::$_primary_column_name;

        $timeStr = gmdate( 'Y-m-d H:i:s');
        if ($autoTimestamp && in_array('created_on',static::getFieldnames())) {
            $this->created_on = $timeStr;
        }
        if ($autoTimestamp && in_array('updated_on',static::getFieldnames())) {
            $this->updated_on = $timeStr;
        }
        $this->validate();
        if ($allowSetPrimaryKey !== true) {
            $this->$pk = null; // ensure id is null
        }
        $query = 'INSERT INTO '.static::_quote_identifier(static::$_tableName).' SET '.$this->setString(!$allowSetPrimaryKey);
        $st = static::execute($query);
        if ($st->rowCount() == 1) {
            $this->{static::$_primary_column_name} = self::$_db->lastInsertId();
        }
        return ($st->rowCount() == 1);
    }

    /**
     * update the current record
     *
     * @param boolean $autoTimestamp true by default will set updated_at field if present
     * @return boolean indicating success
     */
    public function update($autoTimestamp = true) {
        if ($autoTimestamp && in_array('updated_on',static::getFieldnames())) {
            $this->updated_on = gmdate( 'Y-m-d H:i:s');
        }
        $this->validate();
        $query = 'UPDATE '.static::_quote_identifier(static::$_tableName).' SET '.$this->setString().' WHERE '.static::_quote_identifier(static::$_primary_column_name).' = ? LIMIT 1';
        $st = static::execute(
            $query,
            array(
                $this->{static::$_primary_column_name}
            )
        );
        return ($st->rowCount() == 1);
    }

    /**
     * execute
     * connivence function for setting preparing and running a database query
     * which also uses the statement cache
     *
     * @param string $query database statement with parameter place holders as PDO driver
     * @param array $params array of parameters to replace the placeholders in the statement
     * @return PDO statement handle
     */
    public static function execute($query,$params = array()) {
        $st = static::_prepare($query);
        $st->execute($params);
        return $st;
    }

    /**
     * prepare an SQL query via PDO
     *
     * @param string $query
     * @return a PDO prepared statement
     */
    private static function _prepare($query) {
        if ( self::isDatabaseConnected()){
            if (!isset(static::$_stmt[$query])) {
                // cache prepared query if not seen before
                static::$_stmt[$query] = self::$_db->prepare($query);
            }
        return static::$_stmt[$query];  // return cache copy
        }
        return null;
    }

    /**
     * call update if primary key field is present, else call insert
     *
     * @return boolean indicating success
     */
    public function save() {
        if ($this->{static::$_primary_column_name}) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Create an SQL fragment to be used after the SET keyword in an SQL UPDATE
     * escaping parameters as necessary.
     * by default the primary key is not added to the SET string, but passing $ignorePrimary as false will add it
     *
     * @param boolean $ignorePrimary
     * @return string
     */
    protected function setString($ignorePrimary = true) {
        // escapes and builds mysql SET string returning false, empty string or `field` = 'val'[, `field` = 'val']...
        $sqlFragment = false;
        $fragments = array();
        foreach(static::getFieldnames() as $field) {
            if ($ignorePrimary && $field == static::$_primary_column_name) continue;
            if (isset($this->$field)) {
                if ($this->$field === null) {
                    // if empty set to NULL
                    $fragments[] = static::_quote_identifier($field).' = NULL';
                } else {
                    // Just set value normally as not empty string with NULL allowed
                    $fragments[] = static::_quote_identifier($field).' = '.self::$_db->quote($this->$field);
                }
            }
        }
        $sqlFragment = implode(", ",$fragments);
        return $sqlFragment;
    }

    /**
     * convert a date string or timestamp into a string suitable for assigning to a SQl datetime or timestamp field
     *
     * @param string|int $dt a date string or a unix timestamp
     * @return string
     */
    static function datetimeToMysqldatetime($dt) {
        $dt = (is_string($dt)) ? strtotime($dt) : $dt;
        return date('Y-m-d H:i:s',$dt);
    }
}