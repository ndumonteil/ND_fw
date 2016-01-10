<?php
namespace ND\core;

class Request extends Singleton {

    private $_url_argx;

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
