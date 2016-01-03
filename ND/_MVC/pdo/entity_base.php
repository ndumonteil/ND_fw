<?php
namespace ND\MVC;

class Entity_base {
    protected $_data_cache_active= false; // is data cache active ? true or false, default false
    protected $_data_cache_type= 'redis'; // which data cache is used ? default redis
    protected $_id; // properties which stores the _id of entity object deal with
    protected $_use_dto= false; // Defines if a dto must be returned by fetch-like functions

    /**
     * Defines if fetch return have to be dto
     * Should probably moved on entity_base
     * @param bool $_use_dto 
     * @author JB <jbourgeais@orchestra.fr> 
     * @access public
     * @return void
     */
    public function use_dto( $_use_dto=true ){
        $this->_use_dto = $_use_dto;
    }

    public function is_using_dto(){
        return $this->_use_dto;
    }

    /**
    * @param $_id : optional. To set entity's id to deal with
    * @param boolean $_activate_data_cache. To activate cache data for all databasing operations
    * @param string $_data_cache_type. To set the data cache type used for all databasing operations
    **/
    public function __construct($_id = null, $_activate_data_cache = false, $_data_cache_type = null){
        if(! empty($_id)){
            $this->_id = $_id;
        }
        $this->_data_cache_active = $_activate_data_cache;
        if(null !== $_data_cache_type){
            $this->_data_cache_type = $_data_cache_type;
        }
        if(! defined ('SELF_BF')){
            throw new exceptions\non_runtime_e('SELF_BF variable must be defined for logger');
        }
    }

    /**
    * Return all object public properties
    * For the base class, return only entity id, if set during construct.
    **/
    public function get_object(){
        return $this->_id;
    }

    /**
    * return if the data cache is active for databasing operations
    * 
    **/
    public function is_data_cache_active(){
        return $this->_data_cache_active;
    }

    /**
    * return which data cache type the entity uses.
    * 
    **/
    public function get_data_cache_type(){
        return $this->_data_cache_type;
    }

    /**
    * @param $_return array : what mo return when inserting or updating item : array('ok'=>1, 'errmsg''=>'error message', 'n'=> number og*f effected items)
    * return false if an error occured, the number of affectd items if not
    * TODO : deal with log of error code and error message
    **/
    protected function _mo_return_analyze($_return){
        if(!empty($_return['errmsg']) && $_return['ok'] == 0){
            return false;
        }
        return $_return['n'];
    }

}
