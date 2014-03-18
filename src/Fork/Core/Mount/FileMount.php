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

/**
 * Provides abstraction for file CRUD operations on a virtual "Mount"
 * Refer to MountBase for more details.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class FileMount  extends MountObjectAbstract
{

    public function createFile( $fileName, $data )
    {
        return parent::writeObject( $fileName,$data );
    }

    public  function deleteFile( $fileName )
    {
        return parent::deleteObject( $fileName );
    }

    public  function readFile( $fileName )
    {
        return parent::readObject( $fileName );
    }

    public function updateFile( $fileName, $data )
    {
        $dataBuffer = readfile( $fileName );
        $dataBuffer += $data;
        return $this->createFile( $fileName,$dataBuffer );
    }
} 