<?php
namespace ND\MVC;

abstract class Controller {

    protected $_request;
    protected $_response;

    public function __construct( $_request){
        $this->_request= $_request;
    }

}
