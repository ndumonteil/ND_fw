<?php
namespace ND;

use ND\core;
use ND\core\service;
use ND\core\Services_registry;

require_once ND_PATH__FW . '_init' . DIRECTORY_SEPARATOR . 'top_level.php';

require_once ND_PATH__FW_CORE . 'services_registry.php';
require_once ND_PATH__FW_CORE . 'services_loader.php';
require_once ND_PATH__FW_SERVICE . 'configurator.php';

class Kernel {

    const ND_CORE_PREFIX= 'core';
    const ND_APP_PREFIX= 'app';

    private static $_instance;

    private $_app_name;

    private $_services_loader;

    private $_controller;
    private $_action;

    //private $_request;
    //private $_response;

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
        /// CONF
        $configurator= new service\Configurator( $_init_conf);
        Services_registry::add_service( self::ND_CORE_PREFIX, Services_registry::CORE_SERVICE_NAME__CONFIGURATOR, $configurator);
        /// Service loader
        $this->_services_loader= new core\Services_loader();
        /// Begin Init phase
        $this->_services_loader->change_phase( core\Services_loader::EVENT_INIT);
    }

    public function run(){
        $url= filter_input( INPUT_SERVER, 'SERVER_URI');
        $configurator= core\Services_registry::get_instance()
            ->get_service( core\Services_registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $namespace= $configurator->get_init_conf( 'app_controller_namespace');
        $router= core\Services_registry::get_instance()
            ->get_service( core\Services_registry::CORE_SERVICE_NAME__ROUTER);
        $router->set_url( $url);
        $this->_controller_name= $router->get_controller_name();
        $this->_action= $router->get_action_name();
        $request= new core\Request();
        require_once ND_PATH__APP . $this->_app_name . DIRECTORY_SEPARATOR . $this->_controller_name . '.php';
        $controller_class= $namespace . ucfirst( $this->_controller_name);
        /// End Init Phase, Begin Pre_control phase
        $this->_services_loader->change_phase( core\Services_loader::EVENT_PRE_CONTROL);
        $response= ( new $controller_class( $request))->$this->_action();
        /// End Pre_control Phase, Begin Post_control phase
        $this->_services_loader->change_phase( core\Services_loader::EVENT_POST_CONTROL);
        echo $response;
    }

}



