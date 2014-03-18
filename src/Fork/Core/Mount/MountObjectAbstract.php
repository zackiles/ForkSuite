<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Mount;

use Fork\Configuration;
use Fork\Core\Model\MountEntity;
use Fork\Fork;
use Fork\Core\File;
use Hashids\Hashids;
use Fork\Core\File\MimeDetect;
use DotsUnited\Cabinet\Adapter\StreamAdapter;
use Fork\ForkException;


/**
 * A base class used for abstracting file system CRUD operations.
 * Multiple adapters can be used to support different storage needs like AmazonCloud.
 * Mounts are created per client, each client given their own directory named with
 * a hashing function much like youtube video links. Hashes can be encrypted/decrypted
 * to point back to the 'mount' table in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 * @var $mountEntity MountEntity
 */
abstract class MountObjectAbstract
{


    private $mountEntity;
    private static $queue = array();
    private $streamAdapter;

    final function __construct( $mountId )
    {

        // if a mount for this client already exists, the load it
        $this->mountEntity = MountEntity::getById( $mountId );
        // if a mount doesn't exists for this client create it
        if ( ! $this->mountEntity ) {
            throw new ForkException('Unable to find the mount for id : ' . $mountId);
        }

        // Configure the adapter.
        $this->streamAdapter = new StreamAdapter(array(
            'base_path' => self::getAbsoluteMountPathFromRoute($this->mountEntity->route),
            'base_uri'  => $this->getPublicMountRoute(),
            'file_umask'=> Configuration::mountFileMask
        ));
        // Set the class used to detect mime-types for mount objects.
        $this->streamAdapter->setMimeTypeDetector(new MimeDetect());
    }

    final public function adapter(){
        return $this->streamAdapter;
    }


    /**
     * Creates a mount for a client.
     *
     * Mounts are first added to the database, then the mount_id is used
     * to created a hash. This hash is used for the 'route', which is essentially
     * the directory name that is created to contain the clients mounted objects.
     * This route can then provide unauthenticated (no api-key/session)
     * CRUD access in the future if needed.
     *
     * @return MountEntity on success throws exception on failure.
     */
    final public static function createMount()
    {
        // transaction to ensure a directory can be made before we commit the new route to match;
        Fork::Database()->beginTransaction();
        $mountEntity = new MountEntity();
        // first we create a new mount entry
        $mountEntity->insert();
        // after we insert into database, we can use the new mount_id to
        // create a hash for the route.
        $mountEntity->salt = self::generateSalt(); // use a random salt for the hash
        $mountEntity->route = ( new Hashids( $mountEntity->salt ) )
            ->encrypt( $mountEntity->mount_id );
        if ( $mountEntity->update() ) {
            if ( mkdir(
                self::getAbsoluteMountPathFromRoute($mountEntity->route),
                Configuration::mountDirectoryMask ) ) {
                Fork::Database()->commit();
                return $mountEntity;
            }
        }
        // something went wrong, rollback the database changes.
        Fork::Database()->rollBack();
        throw new \Exception('Unable to create the mount entity.');
    }

    final private function queueAddItem( $item )
    {
        // not implemented
        self::$queue[] = $item;
    }

    final private function queueRemoveItem( $item )
    {
        // not implemented
        unset ( self::$queue[$item] );
    }
    final private function processQueue()
    {
        // not implemented
        var_dump( self::$queue );
        return true;
    }

    /**
     * Writes an object to the mount by name.
     *
     * @param string $name
     * @param object $data
     * @return mixed
     */
    final protected function writeObject( $name, $data )
    {
        $this->openMount();
        $result = $this->streamAdapter->write( $name, $data );
        $this->closeMount();
        return $result;
    }

    /**
     * Reads an object from the mount by name.
     *
     * @param string $name
     * @return mixed
     */
    final protected function readObject( $name )
    {
        return $this->streamAdapter->read( $name );
    }

    /**
     * Deletes an object from the mount.
     *
     * @param string $name
     * @return mixed
     */
    final protected function deleteObject( $name )
    {
        return $this->streamAdapter->unlink( $name );
    }

    /**
     * Used for mount opening functions, like file locking etc..
     *
     * @return boolean
     */
    final private function openMount()
    {
        return true;
    }

    /**
     * Used for mount closing functions, like releasing file locks etc..
     *
     * @return boolean
     */
    final private function closeMount()
    {
        return true;
    }

    /**
     * Gets the current mount_id.
     * This is not the same as the route/hash.
     *
     * @return int
     */
    final public function getMountId()
    {
        return $this->mountEntity->mount_id;
    }

    /**
     * Returns the absolute file path that the mount can be accessed from..
     *
     * @return string
     */
    final public static function getAbsoluteMountPathFromRoute($route)
    {
        return Configuration::clientMountRoot . DIRECTORY_SEPARATOR . $route;

    }

    /**
     * Returns a public URL that the mount can be accessed from.
     *
     * @return string
     */
    final public function getPublicMountRoute()
    {
        return Configuration::forkBaseUrl . '/' . $this->mountEntity->route;

    }

    final private static function generateSalt()
    {
        $max = 29;
        $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $i = 0;
        $salt = "";
        while ( $i < $max ) {
            $salt .= $characterList{ mt_rand( 0, ( strlen( $characterList ) - 1) ) };
            $i++;
        }
        return $salt;
    }

}