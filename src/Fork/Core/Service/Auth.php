<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork\Core\Service;

use Fork\Configuration;
use Fork\Core\Client\Client;
use Fork\Core\Client\Role;
use Fork\Fork;
use Fork\StaticFactory;

/**
 * Authorization service class used to load clients/sessions, as well as validate
 * their correct role access for a given api method.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class Auth extends StaticFactory
{
    protected  function __clone()
    {

    }
    protected  function __construct()
    {
        if ( isset($_SESSION['client_id'] ) ) {
            Fork::setClient( ( new Client )->withClientId( $_SESSION['client_id'] ) );
            return $this;
        }
        // api can receive key from both post and get requests.
        if ( isset($_GET['api_key'] ) ) {
            Fork::setClient( ( new Client )->withApiKey( $_GET['api_key'] ) );
            return $this;
        }
        if ( isset($_POST['api_key'] ) ) {
            Fork::setClient( ( new Client )->withApiKey( $_GET['api_key'] ) );
            return $this;
        }
        // If we are allow guest clients, then let's automatically register
        // or load an already registered client  based off their IP address.
        if ( Configuration::allowGuestClients ) {
            //  If this client is already in the system
            if ( ! ClientProvider::getClientByIpAddress( Client::getIpAddress() ) ) {
                // If we aren't using any api keys, as guests should use them.
                // register an account for this client.
                static::autoRegisterGuestClient();
            }
            // catches all requests that don't use api keys.
            if ( ! isset( $_POST['api_key'] ) && ! isset( $_POST['api_key'] ) ) {
                Fork::setClient( ( new Client )->withoutApiKey() );
            }
        }
        return $this;
    }

    /**
     *        Add object to catalog
     *        @return object
     *        @param $roles mixed either an array of int's or int
     **/
    public function isAllowed( $roles )
    {
        $clientRole = intval( Fork::Client()->getRoleId() );
        // Check if the currently connected client is authenticated,
        if ( (bool)Fork::Client()->isAuthenticated ) {
            // if role == admin then = always allowed.
            if ( $clientRole == intval( Role::ADMIN ) ) {
                return true;
            }
            if ( is_array( $roles ) ) {
                foreach( $roles as $role ) {
                    if ( $clientRole == intval( $role ) ) {
                        return true;
                    }
                }
            }
            if ( $clientRole == intval( $roles ) ) {
                return true;
            }
        }
        return false;
    }


    public static function autoRegisterGuestClient()
    {
        if ( ClientProvider::registerWithoutApiKey() ) {
            return true;
        }
        return false;
    }

} 