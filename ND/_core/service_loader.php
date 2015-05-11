<?php
namespace ND\core;

use ND\core\service;

require_once ND_PATH__FW_CORE . 'registry.php';

class Service_loader {

    public function __construct(){}

    public function load_service( $_type, $_service_name){
        $configurator= Registry::get_instance()
            ->get_service( Registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $service_conf= $_type == 'core'
            ? $configurator->get_core_conf( 'services', [ $_service_name])
            : $configurator->get_app_conf( 'services', [ $_service_name]);




    }

}
