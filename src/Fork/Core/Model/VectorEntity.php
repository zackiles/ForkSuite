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
class VectorEntity extends EntityMapper
{
    //overrides
    protected static
        $_tableName = 'vector',
        $_primary_column_name = 'vector_id',
        $createdOn_ColumnName = 'created_on',
        $updatedOn_ColumnName = 'updated_on';

    public
        $vector_id,
        $prong_id,
        $name,
        $description;

}