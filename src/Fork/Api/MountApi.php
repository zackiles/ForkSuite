<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Api;

use Fork\Core\Service\FileMountProvider;
use Fork\Core\File\FileManager;
use Fork\Fork;
use Fork\Strings;
use Luracast\Restler\RestException;
use Fork\Core\Client\Role;

/**
 * This API class provides CRUD access to client Mounts.
 * Mounts can be called either from an authenticated client using:
 * 'site.com/mount/myfile.txt'
 * OR with non authenticated clients, the mount route/hash can be used:
 * 'site.com/aMd65ds/myfile.txt'
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class MountApi
{

    /**
     * Reads a file on the current authenticated clients mount.
     * A current authenticated session is required..
     *
     * @url GET /mount/*
     * @return void
     */
    public function getAuthenticated()
    {
        $mountInstance = static::__mountForCurrentClient();
        $fileName = substr( $_SERVER['REQUEST_URI'], strrpos( $_SERVER['REQUEST_URI'], 'mount\\' ) + 7 );
        $fileName = Strings::mb_str_replace( '/', DIRECTORY_SEPARATOR, $fileName );
        if ( ! $mountInstance->adapter()->exists( $fileName )){
            throw new RestException( 404 );
        }
        $mimeType = $mountInstance->adapter()->type( $fileName );
        $mountInstance = static::__mountForCurrentClient();
        $size = $mountInstance->adapter()->size( $fileName );
        $data = $mountInstance->readFile( $fileName );

        FileManager::sendInline( $fileName, $size, $data, $mimeType );
    }

    /**
     * Reads a file on a clients mount given only the route/hash.
     * No authentication is required.
     *
     *
     * @url GET //*
     * @access public
     * @return void
     */
    public function getAnonymous()
    {
        $mount = func_get_arg( 0 );
        $mountInstance = static::__mountForAnonymous( $mount );
        $requestLength  = mb_strlen( $_SERVER['REQUEST_URI'] ) - mb_strlen( $mount );
        $mountPosition = mb_strrpos( $_SERVER['REQUEST_URI'], $mount ) + mb_strlen( $mount );
        $fileName = mb_substr( $_SERVER['REQUEST_URI'], $mountPosition + 1 , $requestLength );
        $fileName = Strings::mb_str_replace( '/', DIRECTORY_SEPARATOR, $fileName );
        if ( ! $mountInstance->adapter()->exists($fileName)){
            throw new RestException( 404 );
        }
        $mimeType = $mountInstance->adapter()->type( $fileName );
        $size = $mountInstance->adapter()->size( $fileName );
        $data = $mountInstance->readFile( $fileName );

        FileManager::sendInline( $fileName, $size, $data, $mimeType );
    }

    /**
     * Creates a file on the current authenticated clients mount.
     * A current authenticated session is required..
     *
     * @url POST mount
     * @format UploadFormat
     * @return void
     */
    public function postAuthenticated($file)
    {
        $memoryLimit = @ini_get('memory_limit');
        @ini_set( 'max_execution_time', 60 );
        @ini_set( 'upload_max_filesize', $memoryLimit  );
        $mount = static::__mountForCurrentClient();
        foreach ($_FILES as $item) {
            $contents = file_get_contents($item['tmp_name']);
            if ( ! $mount->createFile(basename($item['name']),$contents)) {
                throw new RestException( 500 );
            }
        }
        // Returns nothing but headers. Check status for success - http 200
        flush();
    }

    /**
     * Creates a file on a clients mount given only the route/hash.
     * No authentication is required.
     *
     * @url POST /{route}
     * @access public
     * @return void
     */
    public function postAnonymous($route)
    {
        $mount= static::__mountForAnonymous( $route  );
        $memoryLimit = @ini_get('memory_limit');
        @ini_set( 'max_execution_time', 60 );
        @ini_set( 'upload_max_filesize', $memoryLimit  );
        foreach ($_FILES as $item) {
            $contents = file_get_contents($item['tmp_name']);
            if ( ! $mount->createFile(basename($item['name']),$contents)) {
                throw new RestException( 500 );
            }
        }
        // Returns nothing but headers. Check status for success - http 200
        flush();
    }

    /**
     * Creates a file on a clients mount given only the route/hash.
     * A current authenticated session is required..
     *
     * @url PUT mount/*
     * @return void
     */
    public function putAuthenticated() {
        // PUT body-data comes in on $request_data
        $fileName = substr( $_SERVER['REQUEST_URI'], strrpos( $_SERVER['REQUEST_URI'], 'mount\\' ) + 7 );
        $fileName = Strings::mb_str_replace( '/', DIRECTORY_SEPARATOR, $fileName );
        //  die($fileName);
        $mountInstance = static::__mountForCurrentClient();
        if ( ! $mountInstance->createFile( $fileName, Fork::Router()->requestRawBody ) ) {
            throw new RestException( 500 );
        }
    }

    /**
     * Creates a file on a clients mount given only the route/hash.
     * No authentication is required.
     *
     * @url PUT /*
     * @access public
     * @return void
     */
    public function putAnonymous() {
        $mount = func_get_arg( 0 );
        $mountInstance = static::__mountForAnonymous( $mount );
        $requestLength  = mb_strlen( $_SERVER['REQUEST_URI'] ) - mb_strlen( $mount );
        $mountPosition = mb_strrpos( $_SERVER['REQUEST_URI'], $mount ) + mb_strlen( $mount );
        $fileName = mb_substr( $_SERVER['REQUEST_URI'], $mountPosition + 1 , $requestLength );
        $fileName = Strings::mb_str_replace( '/', DIRECTORY_SEPARATOR, $fileName );
        if ( ! $mountInstance->createFile( $fileName, Fork::Router()->requestRawBody ) ) {
            throw new RestException( 500 );
        }
    }


    /**
     * Creates a file on the current authenticated clients mount.
     *
     * @url get upload
     */
    public function upload()
    {
        $html = "<html>
                    <body>

                    <form action=\"/mount\" method=\"post\"
                    enctype=\"multipart/form-data\">
                    <label for=\"file\">Filename:</label>
                    <input type=\"file\" name=\"file\" id=\"file\"><br>
                    <input type=\"submit\" name=\"submit\" value=\"Submit\">
                    </form>

                    </body>
                    </html> ";
        echo $html;
        @header('Content-Type: text/html');
        ob_end_flush();
    }


    private static function __mountForCurrentClient()
    {
        Fork::Auth( Role::GUEST );
        $mount = Fork::Client()->getFileMount();
        if ( ! $mount ) {

            // Should we throw the exception here, or return false?
            // I figured this is the top level api anyways no need to
            // let it bubble up any further. - Jerome Marshall
            throw new RestException( 500 );
        }
        return $mount;
    }

    private static function __mountForAnonymous( $mountRoute )
    {
        $mount = FileMountProvider::byRoute( $mountRoute );
        if ( ! $mount ) {
            // Should we throw the exception here, or return false?
            // I figured this is the top level api anyways no need to
            // let it bubble up any further. - Jerome Marshall
            throw new RestException( 404 );
        }
        return $mount;
    }

    public function update()
    {

    }

    public function delete()
    {

    }

    /* private function setMount($mount){
         if (strtolower($mount) == 'mount')
         {

             // get the client mount instance.
            $this-> mountInstance = ;
         }
         else
         {

             ;
         }
         if (! $this->mountInstance ){
             throw new \Exception('A mount could not be found');
         }
     }*/

} 