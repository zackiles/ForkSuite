<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Api;

use Fork\Core\Client\Role;
use Fork\Core\Model\ProngEntity;
use Fork\Core\Model\TaskEntity;
use Fork\Core\Prong\Prong;
use Fork\Fork;
use Fork\Core\Task\Task;
use Fork\Configuration;
use Luracast\Restler\RestException;

class Tasks
{
    public function __construct()
    {
        Fork::ProngCache()->loadModulesFromDirectory(Configuration::prongDirectory);
        Task::cleanExpiredTasks();
    }

    /**
     *
     * @url GET /prongs/{prong_id}/tasks/{task_id}/dispatch
     *
     */
    public function dispatchTaskById( $prong_id, $task_id )
    {
        return $this->dispatchTask( $task_id );
    }

    /**
     *
     * @url GET /prongs/{prong_id}/tasks/dispatch/next
     *
     */
    public function dispatchNextTaskByProngId( $prong_id )
    {
        return $this->dispatchTask( $prong_id );
    }

    /**
     *
     * @url GET /prongs/tasks/dispatch/next
     *
     */
    public function dispatchNextTaskByProngKey( $prong_key )
    {
        return $this->dispatchTask( null, $prong_key);
    }

    /**
     *
     * @url GET /prongs/{prong_id}/tasks/{task_id}
     *
     */
    public function getTaskByTaskId( $prong_id, $task_id )
    {
        return $this->getTask( $prong_id, $task_id );
    }


    /**
     *
     * @url GET /prongs/tasks
     *
     */
    public function getAllTasks( $dispatched = null, $responded = null, $prong_key = null )
    {
        Fork::Auth( array( Role::CLIENT, Role::ADMIN ), true );
        if ( ! is_null( $prong_key ) ) {
            if ( ! Fork::ProngCache()->getModuleByKey( $prong_key ) ) {
                throw new RestException( 404 );
            } else {
                if ( false === ( Fork::Client()->getRoleId() == Role::ADMIN ) ) {
                    $prongEntity = Fork::Prong()->openForCurrentClientByKey( $prong_key );
                    if ( ! $prongEntity ) {
                        throw new RestException( 401 );
                    }
                }
            }
        }
        $query = 'SELECT * FROM task';
        $whereClauses = array();
        $orClause = '';
        $params = array();
        if ( false == ( Fork::Client()->getRoleId() == Role::ADMIN ) ) {
            $prongIds = ProngEntity::execute (
                'SELECT prong_id FROM prong
                 WHERE client_id = ?',
                array( Fork::Client()->getClientId() )
            )->fetchAll( \PDO::FETCH_ASSOC );
            foreach ( $prongIds as $id ) {
                if ( empty( $orClause ) )  {
                    $orClause .= ' WHERE (prong_id = ?';
                    $params[] = $id['prong_id'];
                } else {
                    $orClause .= ' OR prong_id = ?';
                    $params[] = $id['prong_id'];
                }
            }
        }
        if ( ! is_null( $dispatched ) ) {
            if (  is_bool( $dispatched ) === false ) {
                throw new RestException( 400 );
            }
            $whereClauses[] = 'dispatched = ?';
            $params[] = $dispatched;
        }
        if ( ! is_null( $responded ) ) {
            if (  is_bool( $responded ) === false ){
                throw new RestException( 400 );
            }
            $whereClauses[] = 'responded = ?';
            $params[] = $responded;

        }
        if ( ! is_null( $prong_key ) ) {
            $whereClauses[] = 'prong_id = ?';
            $params[] = ProngEntity::fetchOneWhere('prong_key = ?', array($prong_key))->prong_id;
        }

        if ( empty( $orClause ) )  {
            for ( $i = 0; $i < count( $whereClauses ); $i++ ) {
                if ( $i == 0 ) {
                    $query .= ' WHERE ' . $whereClauses[$i];
                } else {
                    $query .= ' AND ' . $whereClauses[$i];
                }
            }
        } else {
            $query .= $orClause . ')';
            foreach ( $whereClauses as $clause ) {
                $query .= ' AND ' . $clause;
            }
        }
        $query .= ' ORDER BY task_id ASC';
        $matches = ProngEntity::execute( $query, $params )->fetchAll( \PDO::FETCH_ASSOC );
        if ( ! $matches ) {
            throw new RestException( 404 );
        }

        return $matches;
    }

    private function getTask( $prong_id, $task_id = null )
    {
        Fork::Auth( array( Role::CLIENT, Role::ADMIN ), true );
        if ( false === ( Fork::Client()->getRoleId() === Role::ADMIN ) ) {
            $prongIds = ProngEntity::execute (
                'SELECT prong_id FROM prong
                 WHERE client_id = ?',
                array( Fork::Client()->getClientId() )
            )->fetchAll( \PDO::FETCH_ASSOC );
            $clientProngMatched = false;
            ;
            foreach( $prongIds as $id ) {
                if ( $id['prong_id'] === $prong_id )  $clientProngMatched = true;
            }
            if ( ! $clientProngMatched) throw new RestException( 401 );
        }
        $whereQuery = 'prong_id = ?';
        $params = array($prong_id);
        if ( ! is_null($task_id)) {
            $whereQuery .= ' AND task_id = ?';
            $params[] = $task_id;
        }
        $matches = TaskEntity::fetchOneWhere(
            $whereQuery,
            $params
        );
        if ( ! $matches ) {
            throw new RestException( 404 );
        }
        return $matches;
    }

    private function dispatchTask( $prong_id = null, $prong_key = null )
    {
        Fork::Auth( array( Role::CLIENT, Role::ADMIN ), true );
        $taskEntity = null;
        $prong = null;
        if ( is_null( $prong_key ) ) {
            if ( ! is_null( $prong_id ) ) {
                $this->validateClientProngAccess( $prong_id );
                $prong = Prong::openById( $prong_id );
            } else {
                throw new RestException( 400 );
            }
        } else {
            $prong = Fork::Prong()->openForCurrentClientByKey( $prong_key );
        }
        $taskEntity = Fork::Task()->getNextTaskEntity( $prong, true );
        if ( is_null( $taskEntity ) ) {
            throw new RestException( 404 );
        } else {
            return $taskEntity;
        }
    }

    private function validateClientProngAccess( $prong_id )
    {

        if ( false === ( Fork::Client()->getRoleId() === Role::ADMIN ) ) {
            $prongIds = ProngEntity::execute (
                'SELECT prong_id FROM prong
                 WHERE client_id = ?',
                array( Fork::Client()->getClientId() )
            )->fetchAll( \PDO::FETCH_ASSOC );
            $clientProngMatched = false;
            ;
            foreach( $prongIds as $id ) {
                if ( $id['prong_id'] === $prong_id )  $clientProngMatched = true;
            }
            if ( ! $clientProngMatched) throw new RestException( 401 );
        }
    }

}