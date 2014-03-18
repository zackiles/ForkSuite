<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork\Core\Service;

use Fork\Core\Factory\MountObjectFactory;
use Fork\Core\Mount\FileMount;
use Fork\Core\Model\ClientEntity;
use Fork\Fork;
use Fork\Core\Model\MountEntity;

/**
 * A static service class providing access to client mounts.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class FileMountProvider
{

    public static function forCurrentClient()
    {
        return self::byClientId( Fork::Client()->getClientId() );
    }

    public static function byMountId( $mountId )
    {
        return MountObjectFactory::newFileObject( $mountId );
    }

    public static function byRoute( $route )
    {
        $mountEntity = MountEntity::findOne_by_route( $route );
        if ( ! $mountEntity ) {
            throw new \Exception('Could not find a mount for the route : '.
                $route . " The route does not exist.");
        }
        return static::byMountId( $mountEntity->mount_id );
    }

    public static function byClientId( $clientId )
    {
        $clientEntity = ClientEntity::getById( $clientId );
        if ( ! $clientEntity){
            throw new \Exception('Could not get a mount for the client with id : '.
                $clientId . " The client does not exist.");
        }
        if ( is_null($clientEntity->mount_id)){
            $mountEntity =  FileMount::createMount();
            $clientEntity->mount_id = $mountEntity->mount_id;
            $clientEntity->update(true);
        }

        return static::byMountId( $clientEntity->mount_id );
    }
}