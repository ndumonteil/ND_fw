<?php
namespace ND;

use ND\core;
use ND\core\service;
use ND\core\Services_registry as reg;
use ND\symbol;

require_once ND_PATH__FW . '_init' . DIRECTORY_SEPARATOR . 'top_level.php';

class Kernel {

    private static $_instance;

    private $_services_loader;

    private $_app_name;
    private $_controller_name;
    private $_action_name;

    private $_request;
    private $_response;

    private function __construct(){}
    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public function init( $_init_conf, $_app_name){
        $this->_app_name= $_app_name;
        reg::add_service(
            reg::CORE_SERVICE_NAME__CONFIGURATOR,
            new service\Configurator( $_init_conf)
        );
        $this->_services_loader= new core\Services_loader( $_app_name);
        ///
        // INIT PHASE
        ///
        $this->_services_loader->change_phase( core\Services_loader::EVENT_INIT);
    }

    public function run(){
        $configurator= reg::get_service( reg::CORE_SERVICE_NAME__CONFIGURATOR);
        $namespace= $configurator->get_init_conf( 'app_controller_namespace');
        $router= reg::get_service( reg::CORE_SERVICE_NAME__ROUTER);
        $this->_controller_name= $router->get_controller_name();
        $this->_action_name= $router->get_action_name();
        $this->_request= new core\Request();
        $controller_class= $namespace . ucfirst( $this->_controller_name);
        ///
        // PRE CONTROL PHASE
        ///
        $this->_services_loader->change_phase( core\Services_loader::EVENT_PRE_CONTROL);
        $action= $this->_action_name;
        $this->_response= ( new $controller_class( $this->_request))->$action();
        ///
        // POST CONTROL PHASE
        ///
        $this->_services_loader->change_phase( core\Services_loader::EVENT_POST_CONTROL);
        echo $this->_response;
    }

    public function get_app_name(){
        return $this->_app_name;
    }

    public function get_action_name(){
        return $this->_action_name;
    }

    public function get_controller_name(){
        return $this->_controller_name;
    }

    public function set_action_name( $_action_name){
        $this->_action_name= $_action_name;
    }

    public function set_controller_name( $_controller_name){
        $this->_controller_name= $_controller_name;
    }

}
