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

use Fork\Core\Model\ClientEntity;
use Fork\Core\Model\ClientApiKeyEntity;
use Fork\Core\Client\Client;
use Fork\Core\Client\Role;

/**
 * A collection of static utility functions for client entities..
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ClientProvider
{

    public static function registerWithoutApiKey()
    {
        $clientEntity = static::newClientEntity(Role::CLIENT);
        return static::registerClientEntity( $clientEntity );
    }

    public static function newClientEntity($role)
    {
        // create a new client entity and set the mandatory values
        $clientEntity = new ClientEntity();
        $clientEntity->ip = Client::getIpAddress();
        $clientEntity->role_id = $role;
        $clientEntity->insert();
        return $clientEntity;
    }

    public static function registerClientEntity( ClientEntity $clientEntity )
    {
        $clientEntity->insert();
        $clientEntity = static::getClientById( $clientEntity->client_id );
        return $clientEntity ? $clientEntity : false;
    }

    public static function getCurrentClient()
    {
        $clientEntity =  static::getClientById( $_SESSION['client_id'] );
        return $clientEntity ? $clientEntity : false;
    }

    public static function getClientById( $id )
    {
        $clientEntity = ClientEntity::getById( $id );
        return $clientEntity ? $clientEntity : false;
    }

    public static function getClientByIpAddress( $ip )
    {
        $clientEntity = ClientEntity::findOne_by_ip( $ip );
        return $clientEntity ? $clientEntity : false;
    }

    public static function getClientByApiKey( $apiKey )
    {
        $clientEntity = null;
        if ( $apiKey ) {
            $clientApiKeyEntity = ClientApiKeyEntity::findOne_by_api_key( $apiKey );
            // if an api key exists than a match exists.
            if ( $clientApiKeyEntity ) {
                // set the client-entity to the one who uses this api-key
                // api-keys are to be used only once per client. each key must be unique!
                $clientEntity = ClientEntity::getById( $clientApiKeyEntity->client_id );
            }
        }
        return $clientEntity ? $clientEntity : false;
    }

    public static function addApiKeyForClient( $apiKey, ClientEntity $clientEntity )
    {
        //not implemented
    }

    public static function deleteClientById()
    {
        //not implemented
    }

} 