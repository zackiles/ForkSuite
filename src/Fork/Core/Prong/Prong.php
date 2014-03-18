<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Prong;

use Fork\Core\Model\ProngEntity;
use Fork\Core\Client\Client;
use Fork\Fork;

class Prong
{
    /**
     * A class to represent an active prong connection.
     *
     * @property ProngEntity $prongEntity
     *
     * @author Zachary Iles <zackiles@boxedresearch.com>
     */
    private
        $prongEntity,
        $prongModule;

    private function __construct( ProngEntity $prongEntity, ProngModule $prongModule )
    {
        $this->prongEntity = $prongEntity;
        $this->prongModule = $prongModule;
        if ( ! $this->prongEntity ){
            throw new \Exception (
                'Cannot create a prong, an entity was not provided.' );
        }
        if ( ! $this->prongModule ) {
            throw new \Exception (
                'Cannot create a prong, a module was not provided.' );
        }

        // Updates any per connection prong entity information.
        // This can include any information except the prong_key (which is unique/static)
        $prongEntity->name = $prongModule->name;
        $prongEntity->platform = $prongModule->platform;
        $prongEntity->os = $prongModule->os;
        $prongEntity->domain = $_SERVER['SERVER_NAME'];
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $prongEntity->user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        $prongEntity->update( true );
        return $this;
    }

    /**
     *  Opens a Prong instance with a ProngEntity id.
     *
     *  @return self
     *
     **/
    public static function openById( $prongId )
    {
        $prongEntity = ProngEntity::getById( $prongId );
        if ( ! $prongEntity ){
            throw new \Exception (
                'Cannot create a prong by id, the prong_id was not found.'
            );
        }
        $prongModule = Fork::ProngCache()->getModuleByKey($prongEntity->prong_key);
        if ( !  $prongModule ){
            throw new \Exception (
                'Cannot create a prong by id, ' .
                'the prong module was not found in the prong cache.'
            );
        }
        return new self( $prongEntity, $prongModule );
    }

    /**
     *  Opens a Prong instance for a client, and creates
     *  one if it doesn't already exist.
     *
     *  @return self
     *      *
     **/
    public static function openForClient(Client $client, ProngModule $prongModule)
    {
        // clients should only have one prong per prong_key, but
        // can have multiple prongs. here we only return one prong
        // based on the prong key.
        $prongEntity = ProngEntity::fetchOneWhere(
            'prong_key = ? AND client_id = ?',
             array( $prongModule->prong_key, $client->getClientId() )
        );
        // if a prong doesn't already exist, lets create it.
        if ( ! $prongEntity ) {
            return static::createForClient( $client, $prongModule );
        } else {
            return new self( $prongEntity, $prongModule);
        }
    }

    /**
     *  Creates a prong for a client and returns a Prong instance.
     *
     *  @return self
     *
     **/
    public static function createForClient(Client $client, ProngModule $prongModule)
    {
        $prongEntity = new ProngEntity();
        //  enter all the mandatory columns
        $prongEntity->client_id = $client->getClientId();
        $prongEntity->prong_key = $prongModule->prong_key;
        $prongEntity->name = $prongModule->name;
        $prongEntity->insert( true );
        return new self( $prongEntity, $prongModule );
    }

    /**
     * Returns the current Prong Entities client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->prongEntity->client_id;
    }

    /**
     * Returns the current Prong Entities key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->prongEntity->prong_key;
    }

    /**
     * Returns the current Prong Entities name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->prongEntity->name;
    }

    /**
     * Returns the current Prong Entities id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->prongEntity->prong_id;
    }

    /**
     * Returns a string representing the clients platform.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->prongEntity->platform;
    }

    /**
     * Returns a string representing the clients browser.
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->prongEntity->browser;
    }

    /**
     * Returns a string representing the clients operating system.
     *
     * @return string
     */
    public function getOS()
    {
        return $this->prongEntity->os;
    }

    /**
     * Returns a string representing the clients user agent.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->prongEntity->user_agent;
    }


} 