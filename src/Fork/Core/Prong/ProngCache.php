<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */

namespace Fork\Core\Prong;

use Fork\StaticFactory;
use Fork\ForkException;
use Fork\Core\Task\iTaskHandler;

/**
 * The ProngCache stores prong modules and their config files for usage.
 * Prong modules are defined within json config files with the name prong.json
 *
 *- Each module is stored in it's own directory.
 * -Each module will have the prong.json file stored in it's directory root.
 * -Each module will have its own 'prong_key' that is unique to the prong.
 * -Prong keys are used by connecting clients to match their request with a prong.
 * -Prongs contain unique 'tasks' that clients can receive or call.
 *
 * -Tasks have handler that represent the action. A handler can be a flat file or
 *  a php class file. In the case of a php class file, the task must implement the
 *  iTaskHandler interface. A flat file, will instead be directly sent to the client
 *  on invoking the action. For example if the handler is myfile.js, then the raw
 *  file data will be sent.
 *
 * // Check Fork\Core\Task\Task, or Fork\Core\Task\iTaskHandler for more information.
 *
 * // AN EXAMPLE PRONG MODULE CONFIG FILE
 *   {
 *       "name": "my prong",
 *       "platform": "Desktop",
 *       "os": "Windows",
 *       "prong_key": "mYuNiQueKey",
 *       "version": "1.0.0.0",
 *       "tasks":
 *       [
 *           {
 *           "name": "task 1",
 *           "action": "task1",
 8           "handler": "task1.php"
 *           },
 *           {
 *           "name": "task 2",
 *           "action": "task2",
 *           "handler": "task2.php"
 *           },
 *       ]
 *   {
 *
 *
 * @property $prongConfigStorage an array of prong module configurations
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class ProngCache extends StaticFactory
{

    const PRONG_CONFIG_FILE = "prong.json";
    private $prongConfigStorage = array();

    protected function __construct()
    {
    }

    /**
    *  When given a root directory, iterates through all top
    *  level directories searching for prong config files.
    *  If it finds any, it loads them into the cache if they don't exists..
    *
    *  @return void
    *
    **/
    public function loadModulesFromDirectory( $directory )
    {
        foreach ( new \DirectoryIterator( $directory ) as $fileinfo ) {
            if ( $fileinfo->isDir() && ! $fileinfo->isDot() ) {
                if ( file_exists( $fileinfo->getPathname() ) . DIRECTORY_SEPARATOR . static::PRONG_CONFIG_FILE ) {
                    $this->loadModuleFromConfigFile(
                        $fileinfo->getPathname() . DIRECTORY_SEPARATOR . static::PRONG_CONFIG_FILE
                    );
                }
            }
        }
    }
    /**
     *  Validates that user supplied task handler.
     *
     *  @param $fileName string a task handler file absolute path.
     *  @return bool true on success false on failure.
     *
     **/
    public function validateTaskHandler( $fileName )
    {
        throw new ForkException('Prong()->validateTaskHandler is not implemented.');
    }
    /**
     *  Validates that user supplied prong module config file.
     *
     *  @param $fileName string a prong module config file absolute path.
     *  @return bool true on success false on failure.
     *
     **/
    public function validateProngConfigFile( $fileName )
    {
        throw new ForkException('Prong()->validateTaskHandler is not implemented.');
    }

    /**
     *  Loads a single prong module config file given it's path.
     *
     *  @return void throws exception on failure.
     *
     **/
    public function loadModuleFromConfigFile( $filePath )
    {
        try {
            $data = file_get_contents( $filePath );
            $prong = json_decode( $data );
            $prong->directory = dirname($filePath);
            $this->prongConfigStorage[$prong->prong_key] = $prong;
        } catch (\Exception $ex) {
            throw new ForkException('Enable to load the prong configuration from ' . $filePath, null, $ex);
        }
    }

    /**
     *  Returns a ProngModule object that represents the prong
     *  config file from the cache by it's unique 'prong_key'.
     *
     *  @return ProngModule
     *
     **/
    public function getModuleByKey( $prong_key )
    {
        $prong =  $this->getProngConfigByKey( $prong_key );
        if ( ! $prong ) return false;
        $prongModule = new ProngModule();
        $prongModule->prong_key = $prong->prong_key;
        $prongModule->name = $prong->name;
        @$prongModule->os = $prong->os;
        @$prongModule->platform = $prong->platform;
        @$prongModule->version = $prong->version;
        return $prongModule;
    }

    /**
     *  Returns the handler php class for a task action.
     *  This does not return flat file data, only classes that
     *  implement the iTaskHandler interface.
     *
     *  @return iTaskHandler instance or false on failure
     *
     **/
    public function getTaskHandlerClass( $prong_key, $action )
    {
        $prong =  $this->getProngConfigByKey( $prong_key );
        if ( ! $prong ) return false;
        $taskHandlerPath = '';
        $className = '';
        foreach ( $prong->tasks as $task ) {
            if ( strcmp( $action, $task->action ) == 0) {
                $taskHandlerPath = $prong->directory . DIRECTORY_SEPARATOR . $task->handler;
                $className = basename($taskHandlerPath, '.php');
            }
        }
        if((@include_once $taskHandlerPath) === false) {
            return false;
        }
        return class_exists($className) ? new $className() : false;
    }

    /**
     *  Returns the content type for a task action handler.
     *  Is used to detect the return content for flat files, or
     *  if the handler is php class that implements the iTaskhandler
     *  interface.
     *
     *  @return string
     *
     **/
    public function getTaskHandlerType($handlerFile)
    {
        throw new ForkException('ProngCache->getTaskHandlerType is not implemented.');
    }

    /**
     *  Returns the array entry from the cache for the prong module
     *  given the prongs unique 'prong_key'. Can also be used to validate
     *  a prongs existence in the cache.
     *
     *  @return array or false on failure
     *
     **/
    public function getProngConfigByKey( $prong_key )
    {
        if ( ! isset( $this->prongConfigStorage[$prong_key] ) ) {
            return false;
        }
        return $this->prongConfigStorage[$prong_key];
    }

} 