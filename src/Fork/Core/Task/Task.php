<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Task;

use Fork\Core\Model\LogEntity;
use Fork\Core\Prong\Prong;
use Fork\Core\Model\TaskEntity;
use Fork\Core\Prong\ProngCache;
use Fork\Core\System\Logging;
use Fork\Fork;
use Fork\ForkException;

/**
 * The main controller class for all prong tasks. Used to create tasks, update, render
 * and sync the tasks from prong modules. Tasks are defined with the prong module config files
 * and are stored within the ProngCache. Tasks are called based on their 'action', and their
 * parent prong's 'prong_key'. An action is essentially a unique name for the prong. Clients can access
 * these tasks by providing a 'prong_key' and task 'action' as their tokens.
 *
 * Tasks must implement the Fork\Task\iTaskHandler interface. The methods defined in the interface
 * all must return a value (can be null) and must not be marked void.
 *
 * Task processesing follows the FIFO principles (first in first out). Responses to tasks
 * are assumed for the oldest non expired task that's queued. The same goes for dispatching tasks.
 *
 * @author Jerome Marshall <jeromemarshall@boxedresearch.com>
 */
class Task
{

    /**
     * @var $handlerInstance iTaskHandler
     */
    public $handlerInstance;
    /**
     * @var $prongEntity Prong
     */
    public  $prongInstance;
    /**
     * @var $action string
     */
    public $action;
    /**
     * @var $taskEntity TaskEntity
     */
    private $taskEntity;

    protected function __construct( Prong $prong, $taskAction )
    {
        $this->prongInstance = $prong;
        $this->action = $taskAction;
        $handler = ProngCache::instance()->getTaskHandlerClass( $this->prongInstance->getKey(), $taskAction );
        if ( ! $handler ) {
            throw new \Exception(
                'Prong : ' . $this->prongInstance->getName() . ' with Action : ' . $taskAction .
                ' not found!'
            );
        }
        $this->handlerInstance = $handler;
        return $this;
    }

    public static function cleanExpiredTasks()
    {
        TaskEntity::deleteAllWhere(
            "dispatched = ? AND responded = ? AND expires_on < ?",
            array( true, false, gmdate( 'Y-m-d H:i:s' ) )
        );
    }

    /**
     *  Returns the response payload from the handler.
     *
     *  @return mixed
     *
     **/
    public function getDispatchPayload($markDispatched = false)
    {
        if ($markDispatched){
            $this->markDispatched();
        }
        $data = $this->handlerInstance->dispatch();
        return $data;
    }

    /**
     *  Returns the current database task entity.
     *
     *  @return TaskEntity
     *
     **/
    public function getTaskEntity()
    {
        return $this->taskEntity;
    }

    /**
     *  Receives a response for the task
     *
     *  @return bool throws exception on failure
     *
     **/
    public function processResponse($response)
    {
        $data = $this->handlerInstance->receive($response);
        $responseLog = Logging::writeLog("Task : \"" .
            $this->taskEntity->action .
            "\" started for Client : \"" .
            $this->prongInstance->getClientId() .
            "\" with Prong Data : \"" . var_dump( $this->prongInstance ) );
        $this->taskEntity->response_log_id = $responseLog->log_id;
        $this->taskEntity->response_payload = $data;
        $this->taskEntity->responded = true;
        return $this->taskEntity->update(true) ? true : false;
    }

    /**
     *  Renders an html block that has been formatted by the task handler
     * .
     *  @return string html
     *
     **/
    public function renderTaskResponse()
    {
        return $this->handlerInstance
            ->renderResponse( $this->taskEntity->dispatch_payload );
    }

    /**
     *  Gets the message for the task dispatch log.
     *
     *  @return string log message
     *
     **/
    public function getDispatchLog()
    {
        $logEntity = LogEntity::getById($this->taskEntity->dispatch_log_id);
        return $logEntity->message;
    }

    /**
     *  Gets the message for the task response log.
     *
     *  @return string log message or null if no response received yet.
     *
     **/
    public function getResponseLog()
    {
        $logEntity = LogEntity::getById($this->taskEntity->response_log_id);
        return $logEntity ? $logEntity->message : null;
    }

    private static function open( Prong $prong, TaskEntity $taskEntity )
    {
        $taskInstance = new self( $prong, $taskEntity->action );
        $taskInstance->taskEntity = $taskEntity;
        return $taskInstance;
    }

    /**
     *  Returns a Task instance for the next queued prong task.
     *
     *  @return self or false if no queued tasks exist.
     *
     **/
    public static function openNext( Prong $prong )
    {
        $stmt = TaskEntity::execute(
            'SELECT * FROM task
             WHERE prong_id = ?
             AND dispatched = ?
             AND responded = ?
             ORDER BY task_id ASC LIMIT 1',
            array( $prong->getId(), false, false )
        );
        $stmt->setFetchMode( \PDO::FETCH_OBJ );
        $obj = $stmt->fetch();
        if ( ! $obj ) return false;
        $taskEntity = TaskEntity::getById( $obj->task_id );
        if ( ! $taskEntity ) return false;
        $taskInstance = static::open( $prong, $taskEntity );
        return $taskInstance;
    }

    /**
     *  Marks a task as dispatched to the prong/client.
     *  This does not always mean the client received it, only
     *  that an attempt was made to send it.
     *
     *  @return void
     *
     **/
    public function markDispatched()
    {
        // set the expiry time for the task. tasks which have basses expiry
        // cannot receive responses, and are available for garbage collection.
        $maxTimeOutSeconds = $this->handlerInstance->getTimeOutSeconds();
        $expire_on = new \DateTime( gmdate( 'Y-m-d H:i:s' ) );
        // add the configured seconds onto the current time, and use it for the expiry.
        $expire_on->modify( '+' . strval( $maxTimeOutSeconds ) . ' seconds' );
        $this->taskEntity->expires_on =  $expire_on->format( 'Y-m-d H:i:s' );
        $this->taskEntity->dispatched = true;
        $this->taskEntity->update(true);
    }

    /**
     *  Marks a task as responded/completed..
     *
     *  @return void
     *
     **/
    public function markResponded()
    {
        $this->taskEntity->responded = true;
        $this->taskEntity->update(true);
    }

    /**
     *  Returns a Task instance for an already dispatched task by action.
     *  @return self or false if no tasks with the action exist.
     *
     **/
    public static function openDispatchedByAction( Prong $prong, $taskAction )
    {
        $stmt = TaskEntity::execute(
            'SELECT * FROM task
             WHERE prong_id = ?
             AND dispatched = ?
             AND responded = ?
             ORDER BY task_id ASC LIMIT 1',
            array( $prong->getId(), false, false )
        );
        $stmt->setFetchMode( \PDO::FETCH_OBJ );
        $taskEntity = TaskEntity::getById( $stmt->fetch()->task_id );
        if ( ! $taskEntity ) return false;
        $taskInstance = static::open( $prong, $taskEntity );
        $taskInstance->taskEntity = $taskEntity;
        return $taskInstance;
    }

    public static function openById( $taskId )
    {
        $taskEntity = TaskEntity::getById( $taskId );
        if ( ! $taskEntity ) {
            throw new ForkException(
                'Unable to finding a matching task for task_id : ' . $taskId );
        }
        $prong = Prong::openById( $taskEntity->prong_id );
        $taskInstance = static::open( $prong, $taskEntity );
        $taskInstance->taskEntity = $taskEntity;
        return $taskInstance;
    }

    /**
     *  Add's a task to the prongs task queue.
     *  @return self or false if no tasks with the action exist.
     *
     **/
    public static function newTask( Prong $prong, $taskAction )
    {
        // add a task to a clients prong task queue.
        Fork::Database()->beginTransaction();
        $taskEntity = null;
        $taskInstance = null;
        try {
            $taskInstance = new self( $prong, $taskAction );
            $requestLog = Logging::writeLog( "Task : \"" .
                $taskInstance->action .
                "\" started for Client : \"" .
                $taskInstance->prongInstance->getClientId() .
                "\" on Prong : \"" . $prong->getId() );
            $taskEntity = new TaskEntity();
            $taskEntity->prong_id = $taskInstance->prongInstance->getId();
            // dispatched = 0 = false
            // a task is not dispatched until the client receives the request.
            $taskEntity->dispatched = false;
            // the task can't be responded to as it's new.
            $taskEntity->responded = false;
            $taskEntity->dispatch_payload = $taskInstance->getDispatchPayload();
            $taskEntity->dispatch_log_id = $requestLog->log_id;
            $taskEntity->action = $taskAction;
            // insert the TaskEntity into the database.
            $result = $taskEntity->insert( true );
        } catch( \PDOException $e ) {
            Fork::Database()->rollBack();
            $result = false;
        }
        if ( ! $result ) {
            throw new ForkException(
                'Unable to create to create a task with the action : ' . $taskAction);
        }
        Fork::Database()->commit();
        $taskInstance->taskEntity = $taskEntity;
        return $taskInstance;
    }

} 