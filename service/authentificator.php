<?php
namespace app\service;

class Authentificator {

    private $_confx;

    public function __construct( $_conf){
        $app_name= \ND\Kernel::get_instance()->get_app_name();
        $this->_confx= $_conf;
        /**
         * Vérifier dans la conf si cette URL est soumise à login
         * 
         * Si non soumise à login exit
         * 
         * Si soumise à login vérifier si loggué
         * 
         * Si oui exit
         * 
         * Sinon redirection vers page d'authentification
         * 
         * 
         * 
         * 
         */
    }

    public function is_authenticated(){
        
    }
    
}
