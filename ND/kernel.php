<?php
namespace ND\kernel;

use ND\core;
use ND\core\service;

require_once ND_PATH__FW . '_init' . DIRECTORY_SEPARATOR . 'top_level.php';

require_once ND_PATH__FW_CORE . 'service_loader.php';
require_once ND_PATH__FW_CORE . 'event_listener.php';
require_once ND_PATH__FW_SERVICE . 'configurator.php';

class Kernel {

    private static $_instance;

    private $_app_name;

    private $_service_loader;

    private $_event_listener;

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
        /* @var $registry core\Registry */
        $registry= core\Registry::get_instance();
        $registry->add_service( core\Registry::CORE_SERVICE_NAME__CONFIGURATOR, $configurator);
        /// Service loader


        $this->_event_listener= new core\Event_listener( $_core, $_app_services_conf);
        /// Event listener

/*
        $router_conf= $registry
                ->get_service( core\ND_registry::CORE_SERVICE_NAME__CONFIGURATOR)
                ->get_app_conf( service\ND_app_conf::CONF_NAME__ROUTER, [ $this->_app_name]);
        $router= new service\ND_router( $router_conf);
        $registry->add_service( $registry::CORE_SERVICE_NAME__ROUTER, $router);
        */
        /*
        $logger_conf= $configurator->get_app_conf( service\ND_app_conf::CONF_NAME__LOGGER, [ $this->_app_name]);
        $logger= new service\ND_logger( $logger_conf);
        $this->add_service( self::SERVICE_NAME__LOGGER, $logger);
         * */
    }

    public function run(){

        $this->_event_listener->change_phase( 'pre_control');

        $url= filter_input( INPUT_SERVER, 'SERVER_URI');
        $this->get_service( self::SERVICE_NAME__ROUTER)->set_url( $url);
        $controller_name= $this->get_service( self::SERVICE_NAME__ROUTER)->get_controller_name();
        $action= $this->get_service( self::SERVICE_NAME__ROUTER)->get_action_name();
        $request= new core\Request();
        require_once ND_PATH__APP . $this->_app_name . DIRECTORY_SEPARATOR . $controller_name . '.php';
        $controller_class= 'app\\controller\\' . ucfirst( $controller_name);
        $response= ( new $controller_class( $request))->$action();

        $this->_event_listener->change_phase( 'post_control');

        echo $response;
    }

}



