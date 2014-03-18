<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork\Core\Factory;


use Fork\Core\Mount\FileMount;
use Fork\Core\Task\TaskMount;

/**
 * Static factory used to create a new mount object.
 * All mount objects must extend MountBase
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class MountObjectFactory
{

    private function __construct()
    {
        // Enforce singleton factory with private constructor.
    }

    public static function newTaskObject( $mountId )
    {
        return ( new TaskMount( $mountId ) );
    }

    public static function newFileObject( $mountId )
    {
        return ( new FileMount( $mountId ) );
    }

} 