<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork\Api;

require_once '../src/Fork/Bootstrap.php';

use Fork\Configuration;
use Fork\Fork;
use Luracast\Restler\Defaults;
use Luracast\Restler\Format\UploadFormat;
use Luracast\Restler\Resources;
use Luracast\Restler\Restler;

class Api
{

    public static function start()
    {
        if ( true == Configuration::productionMode ) {
            error_reporting( 0 );
        } else {
            error_reporting( E_ALL );
        }
        self::setContentFormats(Fork::Router());
        self::setServerConfiguration();
        self::setAuthentication(Fork::Router());
        self::setRoutes(Fork::Router());

        // Start Restler and handle the request.
        Fork::Router()->handle();

    }

    private static function setContentFormats(Restler $router)
    {
        // allow files to be uploaded.
        $router->setOverridingFormats('UploadFormat');
        // when allowedMimeTypes is null, all files all mime
        // types for file uploads will be allowed.
        UploadFormat::$allowedMimeTypes = null;
        // Format extensions allow users to post an extension
        // like 'mysite.com/query.json' and force the content type.
        // this is turned off to allow files to be uploaded during PUT
        if ($_SERVER['REQUEST_METHOD'] == 'PUT')
            Resources::$useFormatAsExtension = false;

        if (Configuration::allowTextPlainContent)
            $router->setSupportedFormats(
                'TextFormat',
                'JsonFormat',
                'XmlFormat',
                'JsFormat'
            );

    }


    private static function setRoutes(Restler $router)
    {
        // Set development-mode only api classes/routes.
        if ( false == Configuration::productionMode ) {
            // Class to create Swagger Spec.
            // We can leave off during production.
            $router->addAPIClass('Resources');
            // Class used as a scratch-pad for testing and development.
            $router->addAPIClass('Fork\Api\TestApi','');
        }
     //   $router->addAPIClass('Fork\Api\MountApi','');
      //  $router->addAPIClass('Fork\Api\TaskApi','task');
        $router->addAPIClass('Fork\Api\Tasks','');
    }


    private static function setServerConfiguration()
    {
        ## ------------------------------
        ## SERVER INITIALIZATION
        ## ------------------------------
        @header( 'X-Powered-By: '. Configuration::productName );
        if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
            $_SERVER['SERVER_NAME'] = gethostname();
        }
        if ( function_exists( 'apache_setenv' ) ) {
            // Work around Apache pre-2.4 VirtualDocumentRoot bug
            $_SERVER['DOCUMENT_ROOT'] =
                str_replace( $_SERVER['SCRIPT_NAME'] ,'', $_SERVER['SCRIPT_FILENAME'] );
            apache_setenv( "DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT'] );
        }

        @header('Access-Control-Allow-Origin: *');
        @header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, PATCH, DELETE');
        if(array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
            @header('Access-Control-Allow-Headers: '
                . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        } else {
            @header('Access-Control-Allow-Headers: *');
        }
        ## ------------------------------
        ## PHP INITIALIZATION
        ## ------------------------------
        @ini_set( 'default_charset', $charset='UTF-8' );


        if ( extension_loaded( 'mbstring') ) {
            @mb_internal_encoding( $charset );
        }
        if ( true == Configuration::productionMode ) {
            @ini_set( 'display_startup_errors', 0 );
            @ini_set('display_errors',0);
            @error_reporting( 0 );
        } else {
            ini_set( 'display_startup_errors', 1 );
            ini_set( 'display_errors', 1 );
            ini_set( "error_log", $_SERVER['DOCUMENT_ROOT'] . "/php-error.log" );
            ini_set( "log_errors", 1 );
            error_reporting( E_ALL|E_STRICT );
        }

    }

    private static function setAuthentication(Restler $router)
    {
        // Set the class used to verify access to api methods.
        $router->addAuthenticationClass('Fork\Core\Service\Auth');
    }

}