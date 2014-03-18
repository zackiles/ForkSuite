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
 * Model for a mount entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class MountEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'mount',
        $_primary_column_name = 'mount_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $mount_id,
        $route,
        $salt, // a salt (if needed) used to create the route/hash
        $locked; // true or false.

} 