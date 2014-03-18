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

use Fork\Core\Model\LogEntity;

/**
 * Base class used for logging .
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
abstract class Logging extends LogEventType
{

    /**
     * Add a log to the database
     * isCleanable allows periodic garbage collection.
     * isNotifiable allows priority notification in admin interfaces.
     * Returns a database entity matching the log on success
     *
     * @param  string $message
     * @param LogEventType $logEventType
     * @param $isCleanable bool
     * @param $isNotifiable bool
     * @return LogEntity on success false on failure.
     */
    public static function writeLog($message,
                               LogEventType $logEventType = null,
                               $isCleanable = true,
                               $isNotifiable = false )
    {
        if ( null == $logEventType ) $logEventType = LogEventType::INFO;
        $logEntity = new LogEntity();
        $logEntity->message = $message;
        $logEntity->log_type = intval($logEventType);
        $logEntity->cleanable = $isCleanable;
        $logEntity->notifiable = $isNotifiable;

        return $logEntity->insert(true) ? $logEntity : false;
    }

} 