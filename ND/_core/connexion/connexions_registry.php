<?php
namespace ND\core\connexion;

use ND\exception as e;

final class Connexions_registry {
    
    const CONNEXION_TARGET__MASTER= 'master';
    const CONNEXION_TARGET__SLAVE = 'slave';
    
    private static $_connx;

    private function __construct(){}
    private function __clone(){}

    public static function get_conn( $_context, $_target= self::CONNEXION_TARGET__MASTER){
        if( ! isset( self::$_connx[ $_context][ $_target])){
            // Do connexion
        }
        return self::$_connx[ $_context][ $_target];
    }
    
}
