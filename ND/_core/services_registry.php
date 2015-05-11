<?php
namespace ND\core;

use ND\Kernel;
use ND\exception as e;

class Services_registry {

    const CORE_SERVICE_NAME__CONFIGURATOR= 'core.configurator';
    const CORE_SERVICE_NAME__ROUTER= 'core.router';
    const CORE_SERVICE_NAME__LOGGER= 'core.logger';

    private static $_instance;

    private static $_core_service_names= [
        self::CORE_SERVICE_NAME__CONFIGURATOR,
        self::CORE_SERVICE_NAME__LOGGER,
        self::CORE_SERVICE_NAME__ROUTER,
    ];

    private static $_servicex;

    private function __construct(){}
    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public static function add_service( $_type, $_name, $_instance){
        if( $_type == Kernel::ND_APP_PREFIX && isset( self::$_core_service_names[ $_name])){
            throw new e\name_reserved_e( 'Name %s cannot be used for app service', [ $_name]);
        }
        if( isset( self::$_servicex[ $_name])){
            throw new e\duplicate_service_e( 'Service "%s" intented to be instancied and referenced two times', [ $_name]);
        }
        self::$_servicex[ $_name]= $_instance;
    }

    public static function get_service( $_name){
        return @self::$_servicex[ $_name];
    }

    public static function has_service( $_name){
        return isset( self::$_servicex[ $_name]);
    }


}
