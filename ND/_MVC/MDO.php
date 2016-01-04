<?php
namespace ND\MVC;

/**
 * Memory Data Object
 */
abstract class MDO {

    protected $_cols;

    public function __construct(){}

    public function get( $_key){
        if( isset( $this->$_key)){

        }
    }

    public function set( $_key, $_val){
        $this->$_key= $_val;
    }

    
}
