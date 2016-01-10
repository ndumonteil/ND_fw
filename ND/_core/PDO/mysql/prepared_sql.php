<?php
namespace o\dbals\al;
use o\exceptions as e;

class prepared_sql {
    protected $_sql;
    protected $_is_verb_with_effect;

    public function __construct( $_sql){
        $this->_sql= $_sql;
        $sql= ltrim( $_sql);
        $sqls= explode( ' ', $sql, 2);
        $verb= strtoupper( $sqls[0]);
        switch( $verb){
            case 'SELECT':
            case 'SHOW':
                $this->_is_verb_with_effect= false;
                break;
            case 'UPDATE': // FALLTHRU
            case 'INSERT':
            case 'DELETE':
            case 'TRUNCATE':
                $this->_is_verb_with_effect= true;
                break;
            default:
                throw new e\non_runtime_e( 'unsupported verb= %s', [$verb]);
        }
    }

    public function build(){
        $comment= SELF_BF;
        $sql= $this->_sql;
        $sql= "-- $comment\n$sql";
        return $sql;
    }

    public function is_verb_with_effect(){
        return $this->_is_verb_with_effect;
    }

    /**
     * Builds a sql clause string useable in WHERE and SET clauses
     *  ie. "foo= :foo, bar= :bar"
     * @param array $_x fields
     * @param string $_sep clause separator
     * @access public
     * @return string
     */
    public static function build_prepared_stmt_clause( $_x, $_sep = ' , '){
        $s= [];
        if( 'AND' == $_sep) $s[]= "1\n"; // default WHERE clause to '1': it should never be empty. Typically this should return "1 AND a= 'foo' AND b= 666"
        foreach( array_keys( $_x) as $k){
            $s[]= "$k= :$k";
        }
        $z= implode( $_sep, $s);
        return $z;
    }
    
    /**
     * Builds a sql like clause string useable in WHERE and SET clauses
     *  ie. "foo= :foo, bar= :bar"
     * @param array $_x fields
     * @param string $_operator operotor
     * @access public
     * @return string
     */
    public static function build_prepared_stmt_like_clause( $_x, $_operator){
        $s= "";
        foreach( array_keys( $_x) as $k){
            $s.= " $_operator $k LIKE :$k";
        }
        return $s;
    }
    
    /**
     * Builds an array of arg for "like" prepared stmt
     * @param array $_sel_x field=>value used in where clause
     * @param array $_set_x field=>value used in set clause
     * @access public
     * @return array [ ':foo' => 'fooval', ':bar' => 'barval' ]
     */
    public static function build_prepared_stmt_like_argx( $_sel_x, $_set_x= null){
        trace( null, __FUNCTION__);
        $x= ( $_sel_x?: []);
        if( isset( $_set_x)){
            $x= array_merge( $_sel_x, $_set_x);
        }
        $argx= [];
        foreach( $x as $k=> $v){
            $argx[ ":$k"]= "%$v%";
        }
        return $argx;
    }

    /**
     * Builds an limit clause string
    /**
     * Builds an array of arg for prepared stmt
     * @param array $_sel_x field=>value used in where clause
     * @param array $_set_x field=>value used in set clause
     * @access public
     * @return array [ ':foo' => 'fooval', ':bar' => 'barval' ]
     */
    public static function build_prepared_stmt_argx( $_sel_x, $_set_x= null){
        trace( null, __FUNCTION__);
        $x= ( $_sel_x?: []);
        if( isset( $_set_x)){
            $x= array_merge( $_sel_x, $_set_x);
        }
        $argx= [];
        foreach( $x as $k=> $v){
            $argx[ ":$k"]= $v;
        }
        return $argx;
    }

    /**
     * Builds an limit clause string
     * @param array $_x [ 'nb' => int, 'offset' => int ]
     * @access public
     * @return string 'LIMIT :offset, :nb'
     */
    public static function build_limit_clause( $_x){
        $z= '';
        if( isset( $_x[ 'nb'])){
            $z= 'LIMIT ';
            if( isset( $_x[ 'offset'])){
                $z.= ':offset, ';
            }
            $z.= ':nb';
        }
        return $z;
    }

    /**
     * Builds an order by clause string
     * @param array $_x [ [col=>sort] ]
     * @access public
     * @return string 'ORDER BY :col_1 :sort_1, ...'
     */
    public static function build_order_by_clause( $_x){
        $z= '';
        if(! count($_x)) return $z;
        foreach( $_x as $i=>$order){
            foreach($order as $col=>$sort){
                if( ! in_array(strtolower($sort),["asc","desc"])) continue;
                if( $z) $z.= ',';
                $z.= '`' . $col .'` ' . $sort; // XXX trouver une meilleure protection comme les injections SQL
            }
        }
        if( $z) $z= 'ORDER BY ' . $z;
        return $z;
    }
}
?>
