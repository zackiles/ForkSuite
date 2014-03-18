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
 * Model for a session entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class SessionEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'session',
        $_primary_column_name = 'session_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $session_id,
        $session_hash,
        $data;

} 