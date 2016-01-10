<?php
namespace ND\core;

class Singleton {

    protected static $_instance;

    protected function __construct(){}

    private function __clone(){}
    
    private function __wakeup(){}

    final public static function get_instance(){
        if( ! static::$_instance instanceof static){
            static::$_instance= new static();
        }
        return self::$_instance;
    }
    
}
