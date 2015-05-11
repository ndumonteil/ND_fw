<?php
namespace ND\core\service;

class Router {

    private $_base_conf;
    private $_url_conf;
    private $_request_URL;

    public function __construct( $_conf){
        $this->_base_conf= $_conf;
        $this->_url_conf= $this->_change_keys_with_url( $this->_base_conf);
    }

    public function set_URL( $_URL){
        $this->_request_URL= $_URL;
    }

    public function get_controller_name(){
        $url_parameters= $this->_url_conf[ $this->_request_URL];
        return $url_parameters[ 'controller'];
    }

    public function get_action_name(){
        $url_parameters= $this->_url_conf[ $this->_request_URL];
        return $url_parameters[ 'action'];
    }

    /*public function parse_request(){
        var_dump( $this->_base_conf);
        var_dump( $this->_url_conf);

    }*/

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