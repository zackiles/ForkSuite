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
 * Model for a role entity in the database.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class RoleEntity extends EntityMapper
{
    //overrides
    protected static $_tableName = 'role';
    protected static $_primary_column_name = 'role_id';
    protected static $createdOn_ColumnName = null;
    protected static $updatedOn_ColumnName = null;

    public $role_id;
    public $name;
    public $description;

}