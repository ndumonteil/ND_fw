<?php
namespace app\controller;

use ND\mvc\controller\controller_base;

require_once ND_FW_MVC_PATH . 'controller.php';

class Accueil extends controller_base {

    public function show(){
        return 'Hello World !';
    }

}
