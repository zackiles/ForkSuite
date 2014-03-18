<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 *
 */
namespace Fork;

use \Exception;
use Fork\Core\System\Logging;

class ForkException extends Exception
{

    private $details;

    /**
     * @param string|null $message   error message
     * @param array       $details        any extra detail about the exception
     * @param Exception   $previous       previous exception if any
     */
    public function __construct($message = null, array $details = array(), Exception $previous = null)
    {
        $this->details = $details;
        parent::__construct($message, 0, $previous);
        Logging::writeLog($this->__toString(), Logging::ERROR, false, true);
    }

    /**
     * Get extra details about the exception
     *
     * @return array details array
     */
    public function getDetails()
    {
        return $this->details;
    }

    public function getErrorMessage()
    {
        return $this->getMessage();
    }

    public function getSource()
    {
        $e = $this;
        while ($e->getPrevious()) {
            $e = $e->getPrevious();
        }
        return basename($e->getFile()) . ':'
        . $e->getLine();
    }

}

