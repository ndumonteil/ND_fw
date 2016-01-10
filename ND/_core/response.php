<?php
namespace ND\core;

class Response extends Singleton {

    private $_content;

    public function get_content(){
        return $this->_content;
    }

    public function set_content( $_content){
        $this->_content= $_content;
    }
    
}
