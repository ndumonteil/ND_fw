<?php
namespace ND\core;

use ND\core\Services_registry as SR;

final class Services_loader {

    /**
     * Initialisation of framework
     * Begin : kernel init()
     * End : kernel run()
     */
    const EVENT_INIT= '_init';

    /**
     * Before request processing
     * Begin : kernel run()
     * => after initializing router : services loaded here can re-route the request)
     * End : instanciation of controller in kernel run()
     */
    const EVENT_PRE_CONTROL= '_pre_control';

    /**
     * Before returning response
     * Begin : return controller action in kernel run()
     * => after
     * End : return controller response by kernel
     */
    const EVENT_POST_CONTROL= '_post_control';

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

    private $_service_namespacex= [ 'ND\\service\\'];

    public function __construct(){
        $this->_service_namespacex[]= SR::get_service( SR::CORE_SERVICE_NAME__CONFIGURATION)
                ->get_init_conf( 'app_service_namespace');
    }

    /**
     * Permet d'ajouter des phases durant l'éxecution du contrôleur
     * @param type $_phase
     */
    public function change_phase( $_phase){
        $this->_phase= $_phase;
        $this->_services_to_load= SR::get_service( SR::CORE_SERVICE_NAME__CONFIGURATION)
                ->get( \ND\service\Configuration::CONF_NAME__SERVICES, [ $this->_phase]);
        $this->_loading_finished= empty( $this->_services_to_load);
        while( ! $this->_loading_finished){
            $this->_load_services();
        }
        if( count( $this->_services_to_load)){
            throw new \ND\exception\service_loading_e( 'Services can\'t be loaded because of their dependencies', $this->_services_to_load);
        }
    }

    private function _load_services(){
        $nb_loaded= 0;
        foreach( $this->_services_to_load as $name=> $paramx){
            $confx= isset( $paramx[ 'conf']) ? SR::get_service( SR::CORE_SERVICE_NAME__CONFIGURATION)->get( $paramx[ 'conf']) : null;
            $deps= @$paramx[ 'dependencies'] ?: [];
            if( $this->_check_dependencies( $deps)){
                $class= @$paramx[ 'class'];
                SR::add_service( $name, $this->_load( $name, $class, $confx));
                unset( $this->_services_to_load[ $name]);
                $nb_loaded++;
            }
        }
        if( ! $nb_loaded){
            $this->_loading_finished= true;
        }
    }

    private function _check_dependencies( $deps){
        foreach( $deps as $needed){
            if( ! SR::has_service( $needed)){
                return false;
            }
        }
        return true;
    }
    
    private function _load( $_name, $_class= null, $_confx= null){
        $class= $_class ?: ucfirst( $_name);
        $service_class= $this->_service_namespacex[0] . $class;
        if( ! class_exists( $service_class)){
            $service_class= $this->_service_namespacex[1] . $class;
        }
        return new $service_class( $_confx);
    }

}
