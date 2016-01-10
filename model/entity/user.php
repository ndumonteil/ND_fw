<?php
namespace app\model\entity;

class User extends \ND\MVC\MySQL_entity {
    
    public function __construct(){
        parent::__construct( 'aen', 'User');
    }
    
    
    
}