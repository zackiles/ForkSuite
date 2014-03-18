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


use Fork\Configuration;
use Fork\Core\Model\TaskEntity;
use Fork\Core\Prong\ProngCache;
use Fork\Fork;

/**
 * A scratch pad for quick testing / debugging / dev experiments.
 *
 */
class TestApi
{


    public function prong()
    {
       $task = TaskEntity::execute(
           'SELECT * FROM task WHERE prong_id = ? AND dispatched = ? AND responded = ? ORDER BY task_id ASC LIMIT 1',
           array(1,false,false)
       );
        $task->setFetchMode(\PDO::FETCH_OBJ);
        $a = $task->fetch();
        $taskEntity = TaskEntity::getById($a->task_id);
        if ( ! $taskEntity->dispatched ) {
            echo 'not dispatched';
        } else {
            echo 'dispatched';
        }
        die(var_dump($taskEntity->task_id));
    }


    /**
     * @access protected
     * @class Fork\Core\Service\Auth {@requires_role guest}
     */
    public function test(){
        echo phpinfo();
    }
    /**
     * @access protected
     * @class Fork\Core\Service\Auth {@requires_role guest}
     */
    public function mounttest(){

        echo 'Mount established at' . Fork::Mount()->getPublicMountRoute() . "\n";
        Fork::Mount()->createFile("test.txt","testing");

        echo Fork::Mount()->readFile('test.txt');
    }

    public function mb(){
        //If you're using Apache set "AddDefaultCharset utf-8"
        mb_internal_encoding("UTF-8");
        echo "current mb_internal_encoding: ".mb_internal_encoding();


        /*  mbstring.language   = Neutral   ; Set default language to Neutral(UTF-8) (default)
            mbstring.internal_encoding  = UTF-8 ; Set default internal encoding to UTF-8
            mbstring.encoding_translation = On  ;  HTTP input encoding translation is enabled
            mbstring.http_input     = auto  ; Set HTTP input character set dectection to auto
            mbstring.http_output    = UTF-8 ; Set HTTP output encoding to UTF-8
            mbstring.detect_order   = auto  ; Set default character encoding detection order to auto
            mbstring.substitute_character = none ; Do not print invalid characters
            default_charset      = UTF-8 ; Default character set for auto content type header
        */
    }

    public function unsafeUnicodeFunctions(){
        // http://fatfreeframework.com/web  -- provides unicode auto?
        // https://github.com/Danack/mb_extra
        $unsafeFunctions = array(
            'mail'      => 'mb_send_mail',
            'split'     => null, //'mb_split', deprecated function - just don't use it
            'stripos'   => 'mb_stripos',
            'stristr'   => 'mb_stristr',
            'strlen'    => 'mb_strlen',
            'strpos'    => 'mb_strpos',
            'strrpos'   => 'mb_strrpos',
            'strrchr'   => 'mb_strrchr',
            'strripos'  => 'mb_strripos',
            'strrpos'   => 'mb_strrpos',
            'strstr'    => 'mb_strstr',
            'strtolower'    => 'mb_strtolower',
            'strtoupper'    => 'mb_strtoupper',
            'substr_count'  => 'mb_substr_count',
            'substr'        => 'mb_substr',
            'str_ireplace'  => null,
            'str_split'     => 'mb_str_split', //TODO - check this works
            'strcasecmp'    => 'mb_strcasecmp', //TODO - check this works
            'strcspn'       => null, //TODO - implement alternative
            'stristr'       => null, //TODO - implement alternative
            'strrev'        => 'mb_strrev', //TODO - check this works
            'strspn'        => null, //TODO - implement alternative
            'substr_replace'=> 'mb_substr_replace',
            'lcfirst'       => null,
            'ucfirst'       => 'mb_ucfirst',
            'ucwords'       => 'mb_ucwords',
            'wordwrap'      => null,
        );
    }
    function detectUTF8($string)
    {
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
    }
    public function stack(){
    var_dump(Fork::Stack());
    }

    public function curl(){

        $data = array("name" => "Another", "email" => "another@email.com");
        $data_string = json_encode($data);

        $ch = curl_init('http://restler3.luracast.com/examples/_007_crud/index.php/authors');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        echo($result);
    }

    public function cors(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, PATCH, DELETE');
        header('Access-Control-Max-Age: 1000');
        if(array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
            header('Access-Control-Allow-Headers: '
                . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        } else {
            header('Access-Control-Allow-Headers: *');
        }
    }
} 