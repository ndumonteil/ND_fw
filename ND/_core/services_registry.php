<?php
namespace ND\core;

//use ND\exception as e;

class Services_registry {

    const CORE_SERVICE_NAME__CONFIGURATOR= 'configurator';
    const CORE_SERVICE_NAME__ROUTER= 'router';
    const CORE_SERVICE_NAME__LOGGER= 'logger';

    private static $_instance;

    private $_core_service_names= [
        self::CORE_SERVICE_NAME__CONFIGURATOR,
        self::CORE_SERVICE_NAME__LOGGER,
        self::CORE_SERVICE_NAME__ROUTER,
    ];

    private $_core_servicex;
    private $_app_servicex;

    private function __construct(){}
    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public function add_service( $_name, $_instance){
        if( isset( $this->_core_service_names[ $_name])){
            $this->_core_servicex[ $_name]= $_instance;
        } else {
            $this->_app_servicex[ $_name]= $_instance;
        }
    }

    public function get_service( $_name){
        if( isset( $this->_core_service_names[ $_name])){
            return @$this->_core_servicex[ $_name];
        } else {
            return @$this->_app_servicex[ $_name];
        }
    }

    public function has_service( $_name){
        return isset( $this->_core_servicex[ $_name]) || isset( $this->_app_servicex[ $_name]);
    }

}
