<?php
namespace ND\core;

use ND\symbol;
use ND\core\service;

class Services_loader {

    /**
     * Initialisation of framework
     * Begin : kernel init()
     * End : kernel run()
     */
    const EVENT_INIT= 'init';

    /**
     * Before request processing
     * Begin : kernel run()
     * => after initializing router : services loaded here can re-route the request)
     * End : instanciation of controller in kernel run()
     */
    const EVENT_PRE_CONTROL= 'pre_control';

    /**
     * Before returning response
     * Begin : return controller action in kernel run()
     * => after
     * End : return controller response by kernel
     */
    const EVENT_POST_CONTROL= 'post_control';

    /**
     * Actual phase
     * @var string
     */
    private $_phase;

    /**
     * Temporary array containing all services in a specific phase to be processed
     * @var array
     */
    private $_services_to_load;

    /**
     * Flag to stop loop services loading
     * @var type
     */
    private $_loading_finished;

    private $_service_namespacex= [ 'ND\\core\\service\\'];

    public function __construct(){
        $configurator= Services_registry::get_service( Services_registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $this->_service_namespacex[]= $configurator->get_init_conf( 'app_service_namespace');
    }

    /**
     * Permet d'ajouter des phases durant l'éxecution du contrôleur
     * @param type $_phase
     */
    public function change_phase( $_phase){
        $this->_phase= $_phase;
        $configurator= Services_registry::get_service( Services_registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $this->_services_to_load= $configurator->get( service\Configurator::CONF_NAME__SERVICES, [ $this->_phase]);
        $this->_loading_finished= empty( $this->_services_to_load);
        while( ! $this->_loading_finished){
            $this->_load_services( $configurator);
        }
    }

    private function _load_services( $_configurator){
        if( empty( $this->_services_to_load)){
            $this->_loading_finished= true;
        }
        $nb_of_services_loaded= 0;
        foreach( $this->_services_to_load as $name=> $paramx){
            $confx= isset( $paramx[ 'conf']) ? $_configurator->get( $paramx[ 'conf']) : null;
            $loaded= $this->_try_to_load( $name, $paramx, $confx);
            if( $loaded){
                unset( $this->_services_to_load[ $name]);
                $nb_of_services_loaded++;
            }
        }
        if( ! $nb_of_services_loaded){
            $this->_loading_finished= true;
        }
    }

    private function _try_to_load( $_name, $_paramx, $_confx= null){
        $missing_deps= false;
        if( isset( $_paramx[ 'dependencies'])){
            foreach( $_paramx[ 'dependencies'] as $needed){
                if( ! Services_registry::has_service( $needed)){
                    $missing_deps= true;
                    break;
                }
            }
        }
        if( $missing_deps) return false;
        /// Instanciation
        $class= @$_paramx[ 'class'] ?: ucfirst( $_name);
        $service_class= $this->_service_namespacex[0] . $class;
        if( ! class_exists( $service_class)){
            $service_class= $this->_service_namespacex[1] . $class;
        }
        $service= new $service_class( $_confx);
        ///
        Services_registry::add_service( $_name, $service);
        return true;
    }

}
