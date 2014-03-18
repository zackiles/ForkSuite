<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12/13/13
 * Time: 12:18 PM
 */

namespace Luracast\Restler\Format;


class TextFormat extends Format{

    /**
     * override in the extending class
     */
    const MIME = 'text/plain';
 //   const EXTENSION = 'txt';


    public function encode($data, $humanReadable = false)
    {
        return var_dump($data);
    }
    public function decode($data)
    {
        return $data;
    }
} 