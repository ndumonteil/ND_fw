<?php
namespace ND\MVC;

abstract class MySQL_entity extends Entity_base{

    protected $_table_name;

    /** @var al\conns */
    protected $_conns_o;

    public function __construct( $_ctx_code, $_table_name, al\conns $_al_conns_o = null){
        parent::__construct();
        $this->_table_name= $_table_name;
        ctx\contexts::get_instance()->activate_context($_ctx_code);
        $this->_conns_o= $_al_conns_o?:new al\conns($_ctx_code);
    }

    /**
     * Fetch results in table
     * @param array $_x         Séléction   (Tableau associatif sous la forme [colonne=>valeur]
     * @param array $_cols      Projection  (Tableau sous la forme [colonne1, colonne2, ...]
     * @param array $_order_x   Tri
     * @param Int   $_limit     Nombre de résultats maximums désiré
     * @param Int   $_offset    Offset de début de résultat
     * @return array
     */
    public function fetch( array $_x, array $_cols= [], array $_order_x= [], $_limit= null, $_offset= 0){
        return $this->_fetch( $_x, $_cols, $_order_x, $_limit, $_offset);
    }

    public function count( array $_x){
        trace( func_get_args(), __METHOD__);
        $where_clause= al\prepared_sql::build_prepared_stmt_clause($_x, ' AND ');
        $argx= al\prepared_sql::build_prepared_stmt_argx($_x);
        if( $where_clause) $q.= ' WHERE ' . $where_clause;
        $res= $this->_conns_o->query( $q, [], false);
        return (int)@$res['row_xs'][0]["COUNT(*)"];
    }

    public function count_like( array $_queries_x){
        trace( func_get_args(), __METHOD__);
        $where_clause= " 1 ";
        $sel_x= [];
        foreach( $_queries_x as $i=>$query_x){
            foreach( $query_x as $operator=>$query){
                if( ! in_array(strtolower($operator),["or","and"])) continue;
                // à grandement améliorer en fct des besoins
                $sel_x[key($query)]= current($query);
                $where_clause .= al\prepared_sql::build_prepared_stmt_like_clause($query, ' ' . $operator . ' ');
            }
        }
        $argx= al\prepared_sql::build_prepared_stmt_like_argx($sel_x);
        $q= 'SELECT COUNT(*) FROM `' . $this->_table_name . '`';
        if( $where_clause) $q.= ' WHERE ' . $where_clause;
        $res= $this->_conns_o->query( $q, $argx, false);
        return @$res["row_xs"][0]["COUNT(*)"];
    }


    public function update( array $_sel_x, array $_set_x){
        trace( func_get_args(), __METHOD__);
        return $this->_update( $_sel_x, $_set_x);
    }

    public function insert( array $_x){
        trace( func_get_args(), __METHOD__);
        return $this->_insert( $_x);
    }

    protected function _fetch( array $_sel_x, array $_cols= [], array $_order_x= [], $_limit= null, $_offset= 0){
        trace( func_get_args(), __METHOD__);
        $where_clause= al\prepared_sql::build_prepared_stmt_clause($_sel_x, ' AND ');
        $cols= $_cols?implode( ',', $_cols):"*";
        $argx= al\prepared_sql::build_prepared_stmt_argx($_sel_x);
        $q= 'SELECT ' . $cols . ' FROM `' . $this->_table_name . '`';
        if( $where_clause) $q.= ' WHERE ' . $where_clause;
        $q.= ' ' . al\prepared_sql::build_order_by_clause( $_order_x);
        if( $_limit){
            $q .= ' ' . al\prepared_sql::build_limit_clause( ["nb"=>(int)$_limit,"offset"=>(int)$_offset]);
            $argx["nb"]= (int)$_limit;
            $argx["offset"]= (int)$_offset;
        }
        return $this->_conns_o->query( $q, $argx, false);
    }

    public function _fetch_with_supp_cond( array $_sel_x, array $_cols= [], array $_order_x= [], $_limit= null, $_offset= 0, $_supp_where_cond= null){
        trace( func_get_args(), __METHOD__);
        $where_clause= al\prepared_sql::build_prepared_stmt_clause($_sel_x, ' AND ');
        $cols= $_cols?implode( ',', $_cols):"*";
        $argx= al\prepared_sql::build_prepared_stmt_argx($_sel_x);
        $q= 'SELECT ' . $cols . ' FROM `' . $this->_table_name . '`';
        if( $where_clause | $_supp_where_cond){
            $q.= ' WHERE';
            if( $where_clause){ $q.= " $where_clause";}
            if( isset( $_supp_where_cond)){ $q.= " $_supp_where_cond";}
        }
        $q.= ' ' . al\prepared_sql::build_order_by_clause( $_order_x);
        if( $_limit){
            $q .= ' ' . al\prepared_sql::build_limit_clause( ["nb"=>(int)$_limit,"offset"=>(int)$_offset]);
            $argx["nb"]= (int)$_limit;
            $argx["offset"]= (int)$_offset;
        }
        return $this->_conns_o->query( $q, $argx, false);
    }

    public function fetch_like( array $_queries_x, array $_cols= [], array $_order_x= [], $_limit= null, $_offset= 0){
        trace( func_get_args(), __METHOD__);
        $where_clause= " 1 ";
        $sel_x= [];
        foreach( $_queries_x as $i=>$query_x){
            foreach( $query_x as $operator=>$query){
                if( ! in_array(strtolower($operator),["or","and"])) continue;
                // à grandement améliorer en fct des besoins
                $sel_x[key($query)]= current($query);
                $where_clause .= al\prepared_sql::build_prepared_stmt_like_clause($query, $operator);
            }
        }
        $argx= al\prepared_sql::build_prepared_stmt_like_argx($sel_x);
        $cols= $_cols?implode( ',', $_cols):"*";
        $q= 'SELECT ' . $cols . ' FROM `' . $this->_table_name . '`';
        if( $where_clause) $q.= ' WHERE ' . $where_clause;
        $q.= ' ' . al\prepared_sql::build_order_by_clause( $_order_x);
        if( $_limit){
            $q .= ' ' . al\prepared_sql::build_limit_clause( ["nb"=>(int)$_limit,"offset"=>(int)$_offset]);
            $argx["nb"]= (int)$_limit;
            $argx["offset"]= (int)$_offset;
        }
        return $this->_conns_o->query( $q, $argx, false);
    }

    protected function _insert( array $_x){
        trace( func_get_args(), __METHOD__);
        $q= 'INSERT into `' . $this->_table_name . '` ( ' . implode( ',', array_keys( $_x )) . ' ) VALUES (:' . implode( ',:', array_keys( $_x )) . ')';
        return $this->_conns_o->affect( $q, $_x );
    }

    protected function _update( $_sel_x, $_set_x){
        trace( func_get_args(), __METHOD__);
        foreach( $_sel_x as $sel_key=>$null ) if( isset($_set_x[$sel_key]) ) unset($_set_x[$sel_key]);
        $set_clause = al\prepared_sql::build_prepared_stmt_clause( $_set_x);
        $where_clause = al\prepared_sql::build_prepared_stmt_clause( $_sel_x, ' AND ');
        $argx = al\prepared_sql::build_prepared_stmt_argx( $_sel_x, $_set_x);
        $q= 'UPDATE `' . $this->_table_name . '` SET ' . $set_clause . ' WHERE ' . $where_clause;
        return $this->_conns_o->affect($q, $argx);
    }

    public function delete( array $_sel_x){
        trace( func_get_args(), __METHOD__);
        $where_clause= al\prepared_sql::build_prepared_stmt_clause( $_sel_x, ' AND ');
        $q= 'DELETE FROM `' . $this->_table_name . '` WHERE ' . $where_clause;
        $argx= al\prepared_sql::build_prepared_stmt_argx( $_sel_x);
        return $this->_conns_o->affect( $q, $argx);
    }

    /**
     * teste si une table est vide ou pas
     * @return mixed
     * @throws Exception e\all_e
     */
    public function is_table_empty(){
        trace( func_get_args(), __METHOD__);
        $sql = 'SELECT * FROM `'.$this->_table_name.'` limit 1';
        $res= $this->_conns_o->query( $sql);
        return empty( $res[ 'row_xs']);
    }

}
