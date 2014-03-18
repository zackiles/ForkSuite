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
 * Model for a prong entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ProngEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'prong',
        $_primary_column_name = 'prong_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $prong_id,
        $client_id,
        $user_agent,
        $domain,
        $platform,
        $browser,
        $os,
        $prong_key,
        $name,
        $version;

}