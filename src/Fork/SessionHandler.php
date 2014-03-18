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

/**
 * Custom php session handler. The dynamic entities were not used (entity-mapper), Instead manual queries with
 * bindings were used as an extra security measure against SQLi.
 *
 * @author Zachary Iles <zackiles@boxedresearch.com>
 */
class SessionHandler implements \SessionHandlerInterface
{


    public function open( $savePath, $sessionName )
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    function read( $sessionHash )
    {
        $stmt = Fork::Database()->Prepare( "SELECT data FROM session
        WHERE session_hash = :session_hash FOR UPDATE " );
        $stmt->bindParam( ":session_hash",$sessionHash, \PDO::PARAM_STR );
        $stmt->execute();
        $result =  $stmt->fetch(\PDO::FETCH_ASSOC);
        $data = '' ;
        if ( $result ) {

            $data = $result['data'];
        }
        return $data ;
    }

    public function write( $sessionHash, $data )
    {
        Fork::Database()->beginTransaction();
        try {
        $stmt =Fork::Database()->Prepare( " SELECT count(session_hash)
        AS total FROM session WHERE session_hash = :session_hash" );
        $stmt->bindParam( ":session_hash",$sessionHash, \PDO::PARAM_STR );
        $stmt->execute();
        $result = $stmt->fetch( \PDO::FETCH_ASSOC );
        $total = $result['total'];
        if( $total > 0 ) {
            $stmt2 = Fork::Database()->Prepare( "UPDATE session SET
            data = :data, updated_on = now()
            WHERE session_hash = :session_hash" );
        } else {
            $stmt2 = Fork::Database()->Prepare( "INSERT INTO session
            (session_hash, created_on, updated_on, data)
            VALUES(:session_hash, now(), now(), :data)") ;
        }
        $stmt2->bindParam( ":session_hash",$sessionHash, \PDO::PARAM_STR );
        $stmt2->bindParam( ":data",$data, \PDO::PARAM_STR );
        $stmt2->execute();
        } catch(\PDOException $e) {
            Fork::Database()->rollBack();
        }
        Fork::Database()->commit();
    }

    public function destroy( $sessionHash )
    {
        return true;
    }

    public function gc( $maxlifetime )
    {
        return true;
    }
}
