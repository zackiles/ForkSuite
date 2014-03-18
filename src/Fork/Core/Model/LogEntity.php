<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Model;
use Fork\Core\Data\EntityMapper;

/**
 * Model for an event_log entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class LogEntity extends EntityMapper
{
    //overrides
    protected static $_tableName = 'log',
        $_primary_column_name = 'log_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = null;

    public
        $log_id,
        $log_type,
        $message,
        $cleanable, // true or false.
        $notifiable; // true or false.

} 