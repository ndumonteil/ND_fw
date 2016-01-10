<?php
namespace ND\core;

use ND\exception as e;

final class Services_registry {

    const CORE_SERVICE_NAME__CONFIGURATION= '_core.configuration';
    const CORE_SERVICE_NAME__ROUTER= '_core.router';
    const CORE_SERVICE_NAME__LOGGER= '_core.logger';

    private static $_servicex;

    private function __construct(){}
    private function __clone(){}

    public static function add_service( $_name, $_instance){
        if( isset( self::$_servicex[ $_name])){
            throw new e\service_loading_e( 'The service "%s" has been instantiated and referenced 2 times', [ $_name]);
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
