<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */

namespace Fork\Core\File;

use Fork\Configuration;

class FileManager
{
    public static function sendInline( $filename, $size, $data, $mimeType ){
        ob_end_clean();
        ob_start();
        @header( 'X-Powered-By: '. Configuration::productName );
        //
        //    header('Content-Disposition: inline; filename='.$filename);
        //
        if ( preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
            // Fix for ie
            @header( 'Content-Disposition: inline; filename="' . rawurlencode( $filename ) . '"' );
        } else {
            // Set the dual filename, supporting utf-8 & ascii.
            @header( 'Content-Disposition: inline; filename*=UTF-8\'\'' . rawurlencode( $filename )
                . '; filename="' . rawurlencode( $filename ) . '"' );
        }
        @header('Content-Type:'.$mimeType);
        @header('Content-Length: ' . $size);
        @header('Content-Transfer-Encoding: binary');
        @header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        @header('Pragma: public');
        @header('Expires: 0');

        echo $data;

        ob_end_flush();
        flush();
    }

    public static function receive() {
          }

    /*function sendAttachment($filePath,$mimeType=NULL,$kbps=0) {
        if ( !is_file( $filePath ) )
            return FALSE;
        if ( PHP_SAPI!='cli' ) {
            @header( 'Content-Type:'.$mimeType );
            if ( preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
                // Fix for ie
                @header( 'Content-Disposition: inline; filename="' . rawurlencode( $filename ) . '"' );
            } else {
                // Set the dual filename, supporting utf-8 & ascii.
                @header( 'Content-Disposition: inline; filename*=UTF-8\'\'' . rawurlencode( $filename )
                    . '; filename="' . rawurlencode( $filename ) . '"' );
            }
            @header('Accept-Ranges: bytes');
            @header('Content-Length: '.$size=filesize($filePath));
            @header(Configuration::xPoweredByHeader);
        }
        $ctr=0;
        $handle = fopen($filePath,'rb');
        $start = microtime(TRUE);
        while ( ! feof( $handle ) &&
            ( $info = stream_get_meta_data( $handle ) ) &&
            ! $info['timed_out'] && ! connection_aborted() ) {
            if ( $kbps ) {
                // Throttle output
                $ctr++;
                if ( $ctr/$kbps>$elapsed = microtime( TRUE ) - $start )
                    usleep( 1e6*( $ctr/$kbps-$elapsed ) );
            }
            // Send 1KiB and reset timer
            echo fread($handle,1024);
        }
        fclose($handle);
        return $size;
    }


      public static function serve_file_resumable ($file, $contenttype = 'application/octet-stream') {

        // Avoid sending unexpected errors to the client - we should be serving a file,
        // we don't want to corrupt the data we send
        @error_reporting(0);

        // Make sure the files exists, otherwise we are wasting our time
        if (!file_exists($file)) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }

        // Get the 'Range' header if one was sent
        if (isset($_SERVER['HTTP_RANGE'])) $range = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
        else if ($apache = apache_request_headers()) { // Try Apache again
            $headers = array();
            foreach ($apache as $header => $val) $headers[strtolower($header)] = $val;
            if (isset($headers['range'])) $range = $headers['range'];
            else $range = FALSE; // We can't get the header/there isn't one set
        } else $range = FALSE; // We can't get the header/there isn't one set

        // Get the data range requested (if any)
        $filesize = filesize($file);
        if ($range) {
            $partial = true;
            list($param,$range) = explode('=',$range);
            if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
                header("HTTP/1.1 400 Invalid Request");
                exit;
            }
            $range = explode(',',$range);
            $range = explode('-',$range[0]); // We only deal with the first requested range
            if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
                header("HTTP/1.1 400 Invalid Request");
                exit;
            }
            if ($range[0] === '') { // First number missing, return last $range[1] bytes
                $end = $filesize - 1;
                $start = $end - intval($range[0]);
            } else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
                $start = intval($range[0]);
                $end = $filesize - 1;
            } else { // Both numbers present, return specific range
                $start = intval($range[0]);
                $end = intval($range[1]);
                if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))) $partial = false; // Invalid range/whole file specified, return whole file
            }
            $length = $end - $start + 1;
        } else $partial = false; // No range requested

        // Send standard headers
        header("Content-Type: $contenttype");
        header("Content-Length: $filesize");
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Accept-Ranges: bytes');

        // if requested, send extra headers and part of file...
        if ($partial) {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$filesize");
            if (!$fp = fopen($file, 'r')) { // Error out if we can't read the file
                header("HTTP/1.1 500 Internal Server Error");
                exit;
            }
            if ($start) fseek($fp,$start);
            while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
                $read = ($length > 8192) ? 8192 : $length;
                $length -= $read;
                print(fread($fp,$read));
            }
            fclose($fp);
        } else readfile($file); // ...otherwise just send the whole file

        // Exit here to avoid accidentally sending extra content on the end of the file
        exit;

    }*/
} 