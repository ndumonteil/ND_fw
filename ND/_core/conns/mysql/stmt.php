<?php
namespace o\dbals\al;
use o\exceptions as e, PDO, PDOException;

class stmt {
    protected $_sql;
    protected $_pdo_stmt_o;
    protected $_pdo_o;
    protected $_sql_hash;
    protected $_conn_hash;

    public function __construct( $_sql, $_pdo_o, $_sql_hash, $_conn_hash){
        try {
            note( "My: preparing $_sql_hash on conn=$_conn_hash", $_sql);
            $this->_sql= $_sql;
            $this->_pdo_stmt_o= $_pdo_o->prepare( $_sql);
            $this->_conn_hash= $_conn_hash;
            $this->_sql_hash= $_sql_hash;
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function query( $_x= null){
        try {
            note( "My: executing $this->_sql_hash", $_x);
            return $this->_pdo_stmt_o->execute( $_x);
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function fetch_all(){
        try {
            return $this->_pdo_stmt_o->fetchAll( PDO::FETCH_ASSOC);
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

    public function get_affected_row_nb(){
        try {
            return $this->_pdo_stmt_o->rowCount(); // the number of rows actually updated (not the number of rows selected for the update). Eg it is the 0 in the following Mysql msg: "Rows matched: 1  Changed: 0  Warnings: 2"
        } catch( PDOException $e){
            throw new e\all_e( 'failed : %s', [$e->getMessage()]);
        }
    }

}
?>
