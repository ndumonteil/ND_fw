<?php
namespace ND\core;

class Response {

    private static $_instance;

    private function __construct(){}

    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public function get_content(){
        return $this->_content;
    }

    public function set_content( $_content){
        $this->_content= $_content;
    }
}
