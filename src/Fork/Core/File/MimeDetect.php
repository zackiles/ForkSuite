<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */

namespace Fork\Core\File;

use DotsUnited\Cabinet\MimeType\Detector\DetectorInterface;
use Fork\Configuration;

class MimeDetect implements DetectorInterface{

    /**
     * Detect mime type from a file.
     *
     * @param  string $file
     * @return string
     */
    public function detectFromFile( $file )
    {
        $mimeType = static::getMimeTypeFromCustomList( $file );
        if ( ! $mimeType ) {
            $mimeType = ( new \finfo( FILEINFO_MIME_TYPE ) )->file( $file );

        }
        return $mimeType;
    }

    /**
     * Detect mime type from a string.
     *
     * @param  string $string
     * @return string
     */
    public function detectFromString( $string )
    {
    }

    /**
     * Detect mime type from a resource.
     *
     * @param  resource $resource
     * @return string
     */
    public function detectFromResource( $resource )
    {
    }

    /**
     * Parses and returns a custom mime type list.
     * Removes comments and special characters to create
     * an array of key value pairs. The format of the mimes
     * is the same found in most /etc/mime.types on linux.
     *
     * @param string $file the filename to examine
     * @return array of mime type / extension pairs..
     */
    public static function getCustomMimeTypeList()
    {
        # Returns the system MIME type mapping of extensions to MIME types.
        $out = array();
        $file = fopen( Configuration::mimeTypeList, 'r' );
        while ( ( $line = fgets( $file ) ) !== false ) {
            $line = trim( preg_replace( '/#.*/', '', $line ) );
            if ( ! $line )
                continue;
            $parts = preg_split( '/\s+/', $line );
            if ( count( $parts ) == 1 )
                continue;
            $type = array_shift( $parts );
            foreach( $parts as $part )
                $out[$part] = $type;
        }
        fclose( $file );
        return $out;
    }

    /**
    * Matches mime types by extension to a mime.types file.
    *
    * @param string $file the filename to examine
    * @return string a mime type or null on failure to match.
    */
    public static function getMimeTypeFromCustomList( $filePath ) {
        // Parsing the mime types list is expensive, so we
        // cache the instance.
        static $types;
        if( ! isset ( $types ) )
            $types = static::getCustomMimeTypeList();
        $ext = strtolower( pathinfo( $filePath, PATHINFO_EXTENSION) );
        // Match extensions from the mime type list with
        // $file. If a match exists, return the associated
        // mime-type.
        if ( array_key_exists( $ext, $types ) ) {
            return $types[$ext];
        }
        return false;
    }
} 