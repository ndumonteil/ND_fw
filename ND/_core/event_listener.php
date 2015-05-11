<?php
namespace ND\core;

use ND\core\service as core_service;
use app\service as app_service;

class Event_listener {

    /**
     * Initialisation of framework
     * Begin : kernel init()
     * End : kernel run()
     */
    const EVENT_INIT= 'init';

    /**
     * Before request processing
     * Begin : kernel run()
     * End : instanciation of controller in kernel run()
     */
    const EVENT_PRE_CONTROL= 'pre_control';

    /**
     * Before returning response
     * Begin : running controller action in kernel run()
     * End : return controller response by kernel
     */
    const EVENT_POST_CONTROL= 'post_control';

    private $_phase= self::EVENT_INIT;

    private $_services_to_load;
    private $_loading_finished;

    public function __construct(){}

    /**
     * Permet d'ajouter des phases durant l'éxecution du contrôleur
     * @param type $_phase
     */
    public function change_phase( $_phase){
        $this->_phase= $_phase;
        $configurator= Registry::get_instance()
            ->get_service( Registry::CORE_SERVICE_NAME__CONFIGURATOR);
        $this->_services_to_load= [
            'core'=> $configurator->get_core_conf( 'services', [ $this->_phase]),
            'app'=> $configurator->get_app_conf( 'services', [ $this->_phase]),
        ];
        $this->_loading_finished= false;
        do {
            $this->_load_services();
        } while( ! $this->_loading_finished);
    }

    private function _load_services(){
        if( empty( $this->_services_to_load[ 'core']) && empty( $this->_services_to_load[ 'app'])){
            $this->_loading_finished= true;
        }
        $nb_of_services_loaded= 0;
        foreach( $this->_services_to_load as $type=> $servicex){
            foreach( $servicex as $name=> $paramx){
                $loaded= $this->_try_to_load( $type, $name, $paramx);
                if( $loaded) $nb_of_services_loaded++;
            }
        }
        if( ! $nb_of_services_loaded){
            $this->_loading_finished= true;
        }
    }

    private function _try_to_load( $_type, $_name, $_paramx){
        $missing_deps= false;
        if( isset( $_paramx[ 'dependencies'])){
            foreach( $_paramx[ 'dependencies'] as $needed){
                if( ! Registry::get_instance()->has_service( $needed)){
                    $missing_deps= true;
                    break;
                }
            }
        }
        if( $missing_deps) return false;
        $service_class=
            ( $_type == 'core' ? 'core_service\\' : 'app_service\\')
            . ( @$_paramx[ 'class'] ?: ucfirst( $_name));
        $service_file= @$_paramx[ 'file'] ?: $_name . '.php';
        $service_path=
            ( $_type == 'core' ? ND_PATH__FW_SERVICE : ND_PATH__APP_SERVICE)
            . DIRECTORY_SEPARATOR
            . $service_file;
        require_once $service_path;
        $service= new $service_class;
        Registry::get_instance()->add_service( $_name, $service);
        return true;
    }

}
