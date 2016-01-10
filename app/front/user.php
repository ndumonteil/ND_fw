<?php
namespace app\controller;

class User extends \ND\MVC\Controller {

    public function show_one(){
        return 'show_one';
    }

    public function show_all(){
        $user_entity= new \app\model\entity\User();
        $users= $user_entity->fetch_all();
        var_dump( $users);
        
        return 'show_all';
    }

}
