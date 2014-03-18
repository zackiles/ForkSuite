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
 * Model for a task entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class TaskEntity extends  EntityMapper
{
    //overrides
    protected static
        $_tableName = 'task',
        $_primary_column_name = 'task_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $task_id,
        $prong_id,
        $action,
        $dispatched,
        $responded,
        $dispatch_payload,
        $response_payload,
        $dispatch_log_id,
        $response_log_id,
        $expires_on;

}