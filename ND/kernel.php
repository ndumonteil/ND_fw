<?php
namespace ND;

use ND\core;
use ND\core\service;
use ND\core\Services_registry as reg;

require_once ND_PATH__FW . '_init' . DIRECTORY_SEPARATOR . 'top_level.php';

final class Kernel {

    private static $_instance;

    private $_services_loader;

    private $_app_name;
    private $_controller_name;
    private $_action_name;

    private static $_request;
    private static $_response;

    private function __construct(){
        self::$_request= core\Request::get_instance();
        self::$_response= core\Response::get_instance();
    }

    private function __clone(){}

    public static function get_instance(){
        if( ! self::$_instance instanceof self){
            self::$_instance= new self();
        }
        return self::$_instance;
    }

    public function run( $_app_name, $_init_conf= []){
        $this->_app_name= $_app_name;
        $this->_init( $_init_conf);
        $this->_pre_control();
        $this->_control();
        $this->_post_control();
        echo self::$_response->get_content();
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

    private function _init( $_init_conf){
        reg::add_service(
            reg::CORE_SERVICE_NAME__CONFIGURATOR,
            new service\Configurator( $_init_conf)
        );
        $this->_services_loader= new core\Services_loader( $this->_app_name);
        //------------//
        // INIT PHASE //
        //------------//
        $this->_services_loader->change_phase( core\Services_loader::EVENT_INIT);
    }

    private function _pre_control(){
        $router= reg::get_service( reg::CORE_SERVICE_NAME__ROUTER);
        $router->analyze_url( self::$_request);
        $this->_controller_name= $router->get_controller_name();
        $this->_action_name= $router->get_action_name();
        //-------------------//
        // PRE CONTROL PHASE //
        //-------------------//
        $this->_services_loader->change_phase( core\Services_loader::EVENT_PRE_CONTROL);
    }

    private function _control(){
        $namespace= reg::get_service( reg::CORE_SERVICE_NAME__CONFIGURATOR)
            ->get_init_conf( 'app_controller_namespace');
        $controller_class= $namespace . ucfirst( $this->_controller_name);
        $action= $this->_action_name;
        self::$_response->set_content(
            (new $controller_class( self::$_request))->$action()
        );
    }

    private function _post_control(){
        //--------------------//
        // POST CONTROL PHASE //
        //--------------------//
        $this->_services_loader->change_phase( core\Services_loader::EVENT_POST_CONTROL);
    }

}
