<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Task;


interface iTaskHandler
{

    public static function dispatch();
    public static function receive($response);
    public static function renderResponse($response);
    public static function getTimeOutSeconds();

} 