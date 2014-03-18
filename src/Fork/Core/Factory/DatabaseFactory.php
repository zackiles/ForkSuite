<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Factory;

use Fork\Core\Data\MySqlPDO;

/**
 * Static factory providing a singleton to the DB connection.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class DatabaseFactory
{
    private static $factoryInstance = NULL;

    public static function getFactory()
    {
        if ( ! self::$factoryInstance )
            self::$factoryInstance = new DatabaseFactory();
        return self::$factoryInstance;
    }

    private static $_connectionInstance = NULL;

    public function getConnection()
    {
        if ( ! self::$_connectionInstance ) {
            $mySqlPDO = new MySqlPDO();
            self::$_connectionInstance = $mySqlPDO->dbConnection;
        }
        return self::$_connectionInstance;
    }
}
?>
