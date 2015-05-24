<?php
namespace ND\core\service;

class Router {

    private $_base_conf;
    private $_url_conf;
    private $_request_URL;

    public function __construct( $_conf){
        $app_name= \ND\Kernel::get_instance()->get_app_name();
        $this->_base_conf= array_merge( $_conf[ 'core'], $_conf[ $app_name]);
        $this->_url_conf= $this->_change_keys_with_url( $this->_base_conf);
        $this->_request_URL= filter_input( INPUT_SERVER, 'SERVER_URI');
    }

    public function get_controller_name( $_url= null){
        $url= @$_url ?: $this->_request_URL;
        return $this->_url_conf[ $url][ 'controller'];
    }

    public function get_action_name( $_url= null){
        $url= @$_url ?: $this->_request_URL;
        return $this->_url_conf[ $url][ 'action'];
    }

    public function get_url( $_name, $_argx){
        $_url= $this->_base_conf[ $_name][ 'url'];
        // TODO : parser l'url pour en ressortir les nom d'arguments
        // remplacer les arguments par ceux fournis dans $_argx
    }

    private function _change_keys_with_url( $_base_conf){
        $x= [];
        foreach( $_base_conf as $name=> $route){
            $url= $route[ 'url'];
            unset( $route[ 'url']);
            $route[ 'name']= $name;
            $x[ $url]= $route;
        }
        return $x;
    }

}