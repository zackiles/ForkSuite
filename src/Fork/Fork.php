<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork;

use Fork\Core\Client\Client;
use Fork\Core\Client\Role;
use Fork\Core\Factory\DatabaseFactory;
use Fork\Core\Service\SessionProvider;
use Fork\Core\Model\ClientEntity;
use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Fork\Core\Prong\ProngCache;
use Fork\Core\Service\Auth;
use Fork\Core\Service\ProngProvider;
use Fork\Core\Service\TaskProvider;


/**
 * The global environment for a Fork Suite instance.
 * Provides static methods to access most core services/features.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class Fork
{

    private static
        $clientInstance = null,
        $routerInstance = null;


    //! Prohibit cloning
    private function __clone()
    {
    }

    //! Prohibit instantiation
    private function __construct()
    {
    }

    /**
     * Returns the Auth class instance. or
     * when a $role is provided, it Authorizes
     * access based on the role id provided.
     *
     * @param $role integer a role id
     * @param $throwException boolean true if should throw exception
     *
     * @return Auth instance or bool when $role is provided and throws
     *         exception when $throwException is on.
     */
    public static function Auth( $role = null, $throwException = false )
    {
        if ( ! is_null( $role ) ){
            $authorized = Auth::instance()->isAllowed($role);
            if ( $throwException ) {
                if ( ! $authorized ) {
                    if ( Fork::Client()->isAuthenticated ) {
                        throw new RestException( 403 );
                    } else {
                        throw new RestException( 401 );
                    }
                }
            }
            return (boolean)$authorized;
        }
        return Auth::instance();
    }

    /**
     * Provides access to the ProngProvider service..
     *
     * @return ProngProvider
     */
    public static function Prong()
    {
        return ProngProvider::instance();
    }

    /**
     * Provides access to the TaskProvider service..
     *
     * @return TaskProvider
     */
    public static function Task()
    {
        return TaskProvider::instance();
    }

    /**
     * Provides access to the current prongcache.
     *
     * @return ProngCache
     */
    public static function ProngCache()
    {
        return ProngCache::instance();
    }

    /**
     * Provides access to the current Fork Stack.
     *
     * @return Stack
     */
    public static function Stack()
    {
        return Stack::instance();
    }

    /**
     * Get's an instance to the Router (Restler).
     *
     * @return Restler
     */
    public static function Router()
    {
        if ( ! static::$routerInstance )
            // Start Restler with the user set productionMode flag from Fork Configuration
            static::$routerInstance = ( new Restler( Configuration::productionMode) );
        return static::$routerInstance;
    }

    public static function Database()
    {
        return DatabaseFactory::getFactory()->getConnection();
    }

    /**
     * Provides access to the currently running client session.
     *
     * @return SessionProvider
     */
    public static function Session()
    {
        return SessionProvider::getInstance();
    }

    /**
     * Provides access to the currently connected client.
     *
     * @return Client
     */
    public static function Client()
    {
        return static::$clientInstance ? static::$clientInstance : false;

    }

    /**
     * Sets the currently connected client..
     *
     * @param Client
     * @return boolean
     */
    public static function setClient( Client $client )
    {
        if (!$client)
            return false;
        static::$clientInstance = $client;
        $clientEntity = ClientEntity::getById($client->getClientId());
        // updates the client as to update the current access time.
        $clientEntity->update(true);
        return true;

    }

}
