<?php
namespace ND\core\connexion;

use ND\exception as e;

class Mysql {
    const CONNS_ARE_SHARED= true; // conf
    private $_target2pdo_o, $_target2conn_digest;
    private $_target2is_connected= [ 'slave'=> false, 'master'=> false];
    private $_ctx_code; // ctx code
    private $_ctx_x; // ctx details
    private $_target2stmt_ox= []; // all prepared stmts
    private static $_digest2_pdo_o= [];
    private $_enumx= [];

    public function __construct( $_ctx_code){
        $this->_ctx_code= $_ctx_code;
    }

    private function _connect( $_with_effect, $_target){
        if( $this->_target2is_connected[ $_target]) return true;
        $access_type= ($_with_effect? contexts::W: contexts::R);
        $on_master= ('master'== $_target? 1: 0);
        $this->_ctx_x= \ND\core\connexion\contexts::get_instance()->get_context( $this->_ctx_code, $access_type, $on_master);
        $db= $this->_ctx_x[ 'db'];
        $host= $this->_ctx_x[ 'host'];
        $user= $this->_ctx_x[ 'user'];
        $pass= $this->_ctx_x[ 'pass'];
        $dsn= "mysql:dbname=$db;host=$host;charset=utf8";
        try {
            $s= [$dsn, $user, $pass];
            $digest= hash( 'crc32', implode( '|', $s));
            $this->_target2conn_digest[ $_target]= $digest;
            $o= @self::$_digest2_pdo_o[ $digest];
            if( self::CONNS_ARE_SHARED and $o){
                $this->_target2pdo_o[ $_target]= $o;
            } else {
                note( "My: connecting to " . $this->_target2conn_digest[ $_target], $s);
                $pdo_confx= array(
                        PDO::ATTR_EMULATE_PREPARES => 0,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        );
                $o= new \PDO( $dsn, $user, $pass, $pdo_confx);
                $this->_target2pdo_o[ $_target]= $o;
                self::$_digest2_pdo_o[ $digest]= $o;
            }
        } catch( PDOException $e){
            throw new e\all_e( 'unable to connect to [%s, %s, %s] : '.$e->getMessage(), [$dsn, $user, $pass], $e);
        }
        $this->_target2is_connected[ $_target]= true;
    }

    public function prepare( $_sql_o, $_on_master= false){
        $with_effect= $_sql_o->is_verb_with_effect();
        $target= 'slave';
        if( $_on_master or $with_effect){
            $target= 'master';
        }
        $this->_connect( $with_effect, $target);
        $sql= $_sql_o->build();
        $sql_hash= hash( 'crc32', $sql);
        if( isset( $this->_target2stmt_ox[ $target][ $sql_hash])){
            $stmt_o= $this->_target2stmt_ox[ $target][ $sql_hash];
        } else {
            $stmt_o= new stmt( $sql, $this->_target2pdo_o[ $target], $sql_hash, $this->_target2conn_digest[ $target]);
            $this->_target2stmt_ox[ $target][ $sql_hash]= $stmt_o;
        }
        return $stmt_o;
    }

    public function last_insert_id(){
        if( ! $this->_target2is_connected[ 'master']) throw new e\all_e( 'not connected', []); // this method assumes that a sql stmt was previously executed
        try {
            return $this->_target2pdo_o[ 'master']->lastInsertId();
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function begin_transaction(){
        //note( 'My: starting transaction', __FUNCTION__);
        $this->_connect( $with_effect= true, 'master');
        // Warning inTransaction returns integers not boolean values : http://php.net/manual/en/pdo.intransaction.php
        try {
            if( $this->_target2pdo_o[ 'master']->inTransaction()){
                throw new e\all_e('Fail to start transaction. A transaction already started');
            }
            return $this->_target2pdo_o[ 'master']->beginTransaction(); // autocommit mode is turned off now
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function commit(){
        //note( 'My: committing transaction', __FUNCTION__);
        try {
            $this->_target2pdo_o[ 'master']->commit(); // autocommit mode is turned on now
            return $this->_transaction_rx;
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function rollback(){
        try {
            //note( 'My: rollbacking transaction', '', __FUNCTION__);
            $this->_target2pdo_o[ 'master']->rollback();
            //return an empty array as transaction result because nothing append in database
            return array();
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function query( $_sql, $_argx= null, $_on_master= false){
        $target= ($_on_master? 'master': 'slave');
        try {
            $sql_o= new prepared_sql( $_sql);
            $stmt_o= $this->prepare( $sql_o, $_on_master);
            $stmt_o->query( $_argx);
            $row_xs= $stmt_o->fetch_all();
            $n= $stmt_o->get_affected_row_nb();
            $ret= array( 'affected_row_nb'=> $n, 'row_xs'=> $row_xs);
            if( $this->_target2pdo_o[ $target]->inTransaction()){
                $this->_transaction_rx[]= $ret;
            }
            return $ret;
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    /**
     * @param String $_sql
     * @param array $_argx
     * @return array [affected_row_nb, id]
     * @throws e\all_e
     */
    public function affect( $_sql, $_argx= null){
        try {
            $sql_o= new prepared_sql( $_sql);
            $stmt_o= $this->prepare( $sql_o, $on_master= true);
            $stmt_o->query( $_argx);
            $n= $stmt_o->get_affected_row_nb();
            $id= $this->last_insert_id();
            $ret= array( 'affected_row_nb'=> $n, 'id'=> $id);
            if( $this->_target2pdo_o[ 'master']->inTransaction()){
                $this->_transaction_rx[]= $ret;
            }
            return $ret;
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    /**
     * Vérifie si la valeur est possible par l'enum d'une colonne
     * @param string $_table
     * @param string $_col
     * @param string $_val
     * @return boolean
     */
    public function is_permitted_enum_val( $_table, $_col, $_val){
        if( ! isset( $this->_enumx[ $_table][ $_col])){
            $this->_enumx[ $_table][ $_col]= $this->_fetch_enum_column_permitted_vals( $_table, $_col);
        }
        return isset( $_val, $this->_enumx[ $_table][ $_col]);
    }

    /**
     * Retourne l'enum d'une colonne
     * @param string $_table
     * @param string $_col
     * @return array
     */
    private function _fetch_enum_column_permitted_vals($_table, $_col){
        //trace( [$_table, $_col], __METHOD__);
        $resx= $this->query( "SHOW COLUMNS FROM " . $_table . " LIKE '" . $_col ."'");
        $res= current( $resx[ 'row_xs']);
        $enum= substr( $res[ 'Type'], 6, -2);
        $exploded= explode( "','", $enum);
        return array_flip( $exploded);
    }

    public static function destruct_shared_conns(){
        //trace( null, __FUNCTION__);
        self::$_digest2_pdo_o= [];
    }

}
