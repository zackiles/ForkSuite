<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Service;

use Fork\Core\Task\Task;
use Fork\Core\Prong\Prong;
use Fork\StaticFactory;
use Fork\Core\Model\TaskEntity;

/**
 * A static service class providing access to client prong tasks.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class TaskProvider extends StaticFactory
{

    /**
     *  Returns the next queued task entity and dispatches the task.
     *
     *  @return TaskEntity or false if no available tasks queued
     *
     **/
    public function getNextTaskEntity( Prong $prong, $dispatch = false )
    {
        $task = Task::openNext( $prong );
        if ( ! $task ){
            return false;
        }
        if ( $dispatch ) $task->markDispatched();
        // get the payload and mark as dispatched 'getDispatchPayload(true)'
        return $task->getTaskEntity();
    }

    /**
     *   Returns the next raw queued task payload and dispatches the task.
     *
     *  @return string or false if no available tasks queued
     *
     **/
    public function getNextPayload( Prong $prong, $dispatch = false )
    {
        $task = Task::openNext( $prong );
        if ( ! $task ){
            return false;
        }
        // get the payload and mark as dispatched 'getDispatchPayload(true)'
        return $task->getDispatchPayload( $dispatch );
    }

    /**
     *  Receives the response payload for a clients prong-task.
     *
     *  @return bool true if the payload was handled successfully
     *
     **/
    public function processResponse( Prong $prong, $action, $response )
    {
        $task = Task::openDispatchedByAction($prong, $action);
        if ( ! $task ){
            return false;
        }
       return $task->processResponse($response);

    }

    /**
     *  Creates a new prong-task by action.
     *
     *  @return bool true if the task was created successfully
     *
     **/
    public function createTask( Prong $prong, $action )
    {
        $task = Task::newTask($prong, $action);
        if ( ! $task ){
            return false;
        }
        return $task ? true : false;
    }
} 