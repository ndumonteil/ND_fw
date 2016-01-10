<?php
namespace ND\core\PDO;

abstract class Entity_base {
    
    protected $_context_name; // pour savoir quelle connexion demander 
    protected $_type; // mysql etc. pour savoir quelle connexion effectuer
    protected $_data_cache_active= false; // is data cache active ? true or false, default false
    protected $_data_cache_type= 'redis'; // which data cache is used ? default redis
    protected $_use_MDO= true; // Defines if a dto must be returned by fetch-like functions
    
    public function use_MDO( $_use_MDO=true ){
        $this->_use_MDO = $_use_MDO;
    }

    public function is_using_MDO(){
        return $this->_use_MDO;
    }

    /**
    * @param boolean $_activate_data_cache. To activate cache data for all databasing operations
    * @param string $_data_cache_type. To set the data cache type used for all databasing operations
    **/
    public function __construct( $_context_name, $_activate_data_cache = false, $_data_cache_type = null){
        $this->_context_name= $_context_name;
        $this->_data_cache_active = $_activate_data_cache;
        if(null !== $_data_cache_type){
            $this->_data_cache_type = $_data_cache_type;
        }
    }

    public function is_data_cache_active(){
        return $this->_data_cache_active;
    }

    public function get_data_cache_type(){
        return $this->_data_cache_type;
    }

    protected function get_connexion( $_target){
        return \ND\core\connexion\Connexions_registry::get_conn( $this->_context_name, $_target);
    }
    
}
