<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Client;

use Fork\Configuration;
use Fork\Core\Model\ClientEntity;
use Fork\Core\Model\ProngEntity;
use Fork\Core\Model\RoleEntity;
use Fork\Core\Service\FileMountProvider;
use Fork\Core\Mount\FileMount;
use Fork\Core\Service\SessionProvider;
use Fork\Core\Service\ClientProvider;
use Fork\ForkException;

/**
 * A class to represent an active client connection.
 * Is used as the main facade for client validation, and resource access.
 *
 * @property ClientEntity $clientEntity
 * @property SessionProvider $clientSession
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class Client
{
    private
        $clientEntity = null,
        $clientSession = null;

    public
        $isAuthenticated = false;


    public function __construct( $UNDEFINED = null )
    {
        //  $UNDEFINED - for future IOC
    }

    /**
     * Returns a boolean indicating successful client identification using a client id.
     *
     * @return boolean indicating success
     */
    public function withClientId($client_id)
    {
        $this->clientEntity = CLientEntity::getById($client_id);
        $this->propagateAuthentication();
        return $this;
    }

    /**
     * Returns a boolean indicating successful client identification using only an ip.
     *
     * @return boolean indicating success
     */
    public function withoutApiKey()
    {
        if ( ! Configuration::allowGuestClients ){
            throw new ForkException( 'Unable to authenticate a client '.
            'without an api-key when allowGuestClients is set to false.' );
        }
            // find the client who matches the IP of the current request
            $this->clientEntity = ClientProvider::getClientByIpAddress( $this->getIpAddress() );
            // if a ClientEntity is returned then a match exists.
            $this->propagateAuthentication();

        return $this;
    }

    private function propagateAuthentication()
    {
        if ( $this->clientEntity ) {
            // first set the isAuthentcated property
            $this->isAuthenticated = true;
            // then we can start the session.
            $this->startClientSession();
        }
    }

    /**
     * Returns a boolean indicating successful client identification using an api key.
     *
     * @param string $apiKey
     * @return boolean indicating success
     */
    public function withApiKey( $apiKey )
    {
        // authenticate given only an api key.
        // set the current client to the one who uses this api-key
        // api-keys are to be used only once per client. each key must be unique!
        $this->clientEntity = ClientProvider::getClientByApiKey( $apiKey );
        if ( $this->clientEntity )
            $this->propagateAuthentication();
        return $this;
    }

    /**
     * Starts a session for an authenticated client
     *
     * @return boolean indicating success
     */
    public function startClientSession()
    {
        if ( $this->isAuthenticated ) {
            if ( ! isset( $_SESSION['client_id'] ) ) {
                $this->clientSession = SessionProvider::getInstance();
                if ( $this->clientSession->start() ) {
                    $_SESSION['client_id'] = $this->clientEntity->client_id;
                    return true;
                }
            } else {
                // if a session has already been started, just return true.
                // probably not the best idea.
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a string representing the IP of the remote connection.
     *
     * @return string
     */
    public static function getIpAddress()
    {
        return isset($headers['Client-IP'])?
            $headers['Client-IP']:
            (isset($headers['X-Forwarded-For'])?
                $headers['X-Forwarded-For']:
                (isset($_SERVER['REMOTE_ADDR'])?
                    $_SERVER['REMOTE_ADDR']:''));
    }

    /**
     * Returns a ClientMount instance for the current authenticated client.
     * The ClientMount provides ways to access and manipulate the ClientMountEntity
     *
     * @param string $part
     * @return FileMount or false on failure
     */
    public function getFileMount()
    {
        return FileMountProvider::byClientId( $this->getClientId() );
    }

    /**
     * Returns the role name for the current authenticated client.
     *
     * @return string
     */
    public function getRoleName()
    {
        return RoleEntity::getById( $this->getRoleId() )->name;
    }

    /**
     * Returns the role_id for the current authenticated client.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->clientEntity->role_id;

    }

    /**
     * Returns the client_id for the current authenticated client.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientEntity->client_id;

    }

    /**
     * Returns the client description, if any.
     * A description is a unique tag/label for a client.
     *
     * @return int
     */
    public function getDescription()
    {
        return $this->clientEntity->description;

    }

    /**
     * Returns the client_id for the current authenticated client.
     *
     * @return array
     */
    public function getProngs()
    {
        $prongs = ProngEntity::fetchAllWhere('client_id = ?', array($this->getClientId()));
        return $prongs;

    }

}