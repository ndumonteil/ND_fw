<?php
namespace ND\core;

class Request {

    private static $_instance;
    private $_url_argx;

    private function __construct(){}
    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public function get_requested_url(){
        return filter_input( INPUT_SERVER, 'REQUEST_URI');
    }

    public function set_url_argx( $_url_argx){
        $this->_url_argx= $_url_argx;
    }

    public function get_url_arg( $_key){
        return @$this->_url_argx[ $_key];
    }
}
