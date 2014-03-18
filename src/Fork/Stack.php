<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */

namespace Fork;


class Stack extends StaticFactory{
    private $stackCache = array();
    //! Prohibit cloning
    private function __clone() {
    }

    //! Prohibit instantiation
    protected function __construct() {
        $headers = array();
        if ( PHP_SAPI != 'cli' ) {
            foreach ( array_keys( $_SERVER ) as $key ){
                if ( substr( $key,0,5 )=='HTTP_' ) {
                    $headers [ strtr( ucwords( strtolower( strtr(
                        substr( $key, 5 ), '_', ' ') ) ), ' ', '-' ) ] = & $_SERVER[$key];
                }
            }
        }
        $this->stackCache = array(
            'AJAX'=> isset($headers['X-Requested-With']) &&
                $headers['X-Requested-With']=='XMLHttpRequest',
            'HEADERS' => $headers,
            'BODY' => file_get_contents('php://input'),
            'HOST'=>$_SERVER['SERVER_NAME'],
            'LISTENERS' => array(),
            'PLUGINS' => strtr( Configuration::prongDirectory,'\\','/' ) .'/',
            'BASE'=> preg_replace('/\/[^\/]+$/','',$_SERVER['SCRIPT_NAME']),
            'SERIALIZER'=> extension_loaded($ext='igbinary')?$ext:'php',
        );

    }
    public function get($key = null) {
        if ( ! $key ) {
            return $this->stackCache;
        }
        return $this->stackCache[$key];
    }
    public function set($key = null, $value = null) {
        $this->stackCache[$key] = $value;
    }
} 