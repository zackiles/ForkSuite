<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\System;

/**
 * Varying event types for the log entries.
 * Using integers for DB entries allows better performance
 * when querying for specific log types.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
abstract class LogEventType
{

    const CONNECTION = 0;
    const SESSION_START = 1;
    const PRONG_START = 2;
    const MOUNT_ACCESSED = 3;
    const TASK_CREATED = 4;
    const TASK_SENT = 5;
    const TASK_RECEIVED = 6;
    const TASK_COMPLETE = 7;
    const ADMIN_LOGIN = 8;
    const ADMIN_ACTION = 9;
    const ERROR = 10;
    const INFO = 11;

}