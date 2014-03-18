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
 * Model for a client entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ClientEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'client',
        $_primary_column_name = 'client_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $client_id,
        $role_id,
        $mount_id,
        $ip,
        $geo_location,
        $description;

} 