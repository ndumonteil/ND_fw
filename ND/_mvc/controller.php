<?php
namespace ND\mvc;

abstract class Controller {

    private $_request;
    private $_response;

    public function __construct( $_request){
        $this->_request= $_request;
    }



}
