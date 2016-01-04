<?php
namespace app\controller;

class Accueil extends \ND\MVC\Controller {

    public function show(){
        return 'Hello World !';
    }

    public function show_me(){
        return 'Hello ' . $this->_request->get_url_arg( 'name') . ' !';
    }

    public function show_us(){
        return 'Hello ' . $this->_request->get_url_arg( 'name') . ' ! I am ' . $this->_request->get_url_arg( 'name2');
    }
}
