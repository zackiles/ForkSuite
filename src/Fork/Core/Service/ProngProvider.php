<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */

namespace Fork\Core\Service;

use Fork\Core\Prong\Prong;
use Fork\Fork;
use Fork\Core\Client\Client;
use Fork\StaticFactory;
use Fork\Core\Prong\ProngModule;
/**
 * A static service class providing access to client prongs.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ProngProvider extends StaticFactory
{

    /**
     *  Creates a ProngModule instance with a 'prong_key'
     *  as long as the prong-module is currently loaded in
     *  the ProngCache.
     *
     * @param $client Client instance
     * @param $key string a prong-module key.
     *  @return ProngModule instance on success
     *
     **/
    public function getModuleByKey( Client $client, $prong_key )
    {
        $prongModule = Fork::ProngCache()->getModuleByKey( $prong_key );
        if ( ! $prongModule ) return false;
        return Prong::openForClient(
            $client,
            $prongModule
        );
    }

    /**
     *  Opens a prong by prong-key for the current client or
     *  creates one if it does not exist.
     *
     * @param $key string a prong-module key.
     *  @return Prong instance on success
     *
     **/
    public function openForCurrentClientByKey( $prong_key )
    {
        $prongModule = Fork::ProngCache()->getModuleByKey( $prong_key );
        if ( ! $prongModule ) return false;
        return Prong::openForClient(
            Fork::Client(),
            $prongModule
        );
    }

} 