<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Service;

use Fork\Core\Client\Client;
use Fork\ForkException;
use Fork\SessionHandler;

/**
 * Php session handler backed by a PDO DB connection. Mutli-db platform capable..
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class SessionProvider
{

    private static $_sessionInstance;
    private static $_sessionHandler;
    private static $_initialized = false;

    public static function getInstance( ) {

        if ( ! self::$_sessionInstance ) {
            self::$_sessionInstance = new self();
            self::$_sessionHandler = new SessionHandler( );
        }

        return self::$_sessionInstance;
    }

    public function start( $lifetime = 30, $path = '/', $domain = NULL, $httpOnly = false, $secure = false )
    {
        if ( ! self::$_initialized ) {
            if ( session_id() == '' ) {
                ini_set( 'session.save_handler', 'user' );
                ini_set( 'session.gc_maxlifetime', $lifetime );
                ini_set( 'session.gc_probability', '100' );
                ini_set( 'session.gc_divisor', '100' );
            }
            if ( ! session_set_save_handler(self::$_sessionHandler, true ) ) {
                throw new ForkException( "Failure to set the session handler." );
            }
            session_set_cookie_params( $lifetime, $path, ($domain ? $domain : self::getDomain()), $secure, $httpOnly );
            if ( session_id() == '' ) {
                if ( headers_sent() ) {
                    throw new ForkException( 'Headers already sent. Cannot start session.' );
                }

                session_id( md5( Client::getIpAddress() ) );
                session_name( "FORKSESSION" );
                session_start();
            }

            self::$_initialized = true;
        }
        return session_id();
    }

    public static function getDomain()
    {
        if( isset($_SERVER['HTTP_HOST']) ) {

            if( preg_match('/(localhost|127\.0\.0\.1)/', $_SERVER['HTTP_HOST'] )
                || $_SERVER['SERVER_ADDR'] == '127.0.0.1' ) {
                return null; // prevent problems on local setups
            }
            return preg_replace('/(^www\.|:\d+$)/i', NULL, $_SERVER['HTTP_HOST'] );
        }
        return null;
    }


/*
    private function _isSessionActive() {
        try {
            $sql = $this->_con->prepare('SELECT client_id FROM session WHERE session_hash = :sessionHash LIMIT 1');
            $sql->bindParam(':sessionHash', $sessionHash, PDO::PARAM_STR, 32);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $this->sessionHash = $sql->fetch(PDO::FETCH_ASSOC);
                return true;
            }
                return false;
        } catch (PDOException $e) {
            throw new \Luracast\Restler\RestException(501, 'MySQL: ' . $e->getMessage());
        }
    }

    private function getMountPointFromClientId($clientId) {

    }
*/

}