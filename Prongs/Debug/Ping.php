<?php

class Ping implements Fork\Core\Task\iTaskHandler{

    public static function dispatch()
    {
        return 'hello from Debug()->Ping';
    }

    public static function receive($response)
    {
        return $response;
    }

    public static function renderResponse($response)
    {
        return $response;
    }

    public static function getTimeOutSeconds()
    {
        return 30;
    }
} 