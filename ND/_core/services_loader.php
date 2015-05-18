<?php
namespace ND\core;

use ND\Kernel;
use ND\symbol;

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

    private $_service_pathx= [
        symbol::PREFIX_CORE=> ND_PATH__FW_SERVICE,
        symbol::PREFIX_APP=> ND_PATH__APP_SERVICE,
    ];

    private $_service_namespacex= [
        Kernel::ND_CORE_PREFIX=> 'ND\\core\\service\\',
    ];

    public function __construct(){}

    /**
     * Permet d'ajouter des phases durant l'éxecution du contrôleur
     * @param type $_phase
     */
    public function change_phase( $_phase){
        $this->_phase= $_phase;
        $configurator= Services_registry::get_service(
            Services_registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $this->_services_to_load= [
            Kernel::ND_CORE_PREFIX=> $configurator->get_core_conf( service\Configurator::CONF_NAME__SERVICES, [ $this->_phase]),
            Kernel::ND_APP_PREFIX=> $configurator->get_app_conf( service\Configurator::CONF_NAME__SERVICES, [ $this->_phase], true),
        ];
        $this->_loading_finished= false;
        do {
            $this->_load_services();
        } while( ! $this->_loading_finished);
    }

    private function _load_services(){
        if( empty( $this->_services_to_load[ Kernel::ND_CORE_PREFIX])
            && empty( $this->_services_to_load[ Kernel::ND_APP_PREFIX])){
            $this->_loading_finished= true;
        }
        $nb_of_services_loaded= 0;
        foreach( $this->_services_to_load as $type=> $servicex){
            foreach( $servicex as $name=> $paramx){
                $loaded= $this->_try_to_load( $type, $name, $paramx);
                if( $loaded){
                    unset( $this->_services_to_load[ $type][ $name]);
                    $nb_of_services_loaded++;
                }
            }
        }
        if( ! $nb_of_services_loaded){
            $this->_loading_finished= true;
        }
    }

    private function _try_to_load( $_type, $_name, $_paramx){
        $missing_deps= false;
        if( isset( $_paramx[ 'dependencies'])){
            var_dump( $_paramx);
            foreach( $_paramx[ 'dependencies'] as $needed){
                if( ! Services_registry::has_service( $needed)){
                    $missing_deps= true;
                    break;
                }
            }
        }
        if( $missing_deps) return false;
        /// Instanciation
        $service_class=
            $this->_service_namespacex[ $_type]
            . ( @$_paramx[ 'class'] ?: ucfirst( $_name));
        $service_file= @$_paramx[ 'file'] ?: $_name . '.php';
        $service_path=
            $this->_service_pathx[ $_type]
            . DIRECTORY_SEPARATOR
            . $service_file;
        require_once $service_path;
        $service= new $service_class();
        ///
        Services_registry::add_service( $_type, $_name, $service);
        return true;
    }

}
