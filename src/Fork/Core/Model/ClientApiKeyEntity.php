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
 * Model for a ClientApiKeyEntity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ClientApiKeyEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'client_api_key',
        $_primary_column_name = 'client_api_key_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $client_api_key_id,
        $api_key,
        $client_id;

} 