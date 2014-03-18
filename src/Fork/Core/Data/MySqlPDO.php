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
use Fork\ForkException;

class MySqlPDO
{

    public $dbConnection;

    public function __construct()
    {
        try {
            $options = array( \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', \PDO::ATTR_PERSISTENT => true) ;
            $this->dbConnection = new \PDO (
                'mysql:host=localhost;dbname=fork_db',
                'root',
                'aaaaaa',
                $options
            );
            $this->dbConnection->setAttribute( \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_ERRMODE );
        } catch ( \PDOException $ex ) {
            throw new ForkException( 'Unable to connect to the database', null, $ex  );
        }
    }

    /**
     * return the driver name for the current database connection
     *
     * @return string (driver name as returned by PDO)
     */
    public static function getDriverName( $connection )
    {
        return $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * return the PDO drivers delimited quote character. (eg backtick for mysql etc)
     *
     * @return string (quote character)
     */
    public static function getDriverQuoteCharacter( $connection )
    {
        $quoteChar = '`'; //default
        switch( static::getDriverName( $connection ) ) {
            case 'pgsql':
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
            case 'sybase':
                $quoteChar = '"';

        }
        return $quoteChar;
    }

    public function __destruct()
    {
        if ( ! $this->dbConnection )
            $this->dbConnection = null;
    }

}

?>
