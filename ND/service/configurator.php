<?php
namespace ND\core\service;

use ND\exception as e;
use ND\tool\fh;
use ND\tool\util;

require_once ND_PATH__FW_TOOL . 'fh' . DIRECTORY_SEPARATOR . 'file.php';

class Configurator {

    const CONF_NAME__DATABASES= 'databases';
    const CONF_NAME__ROUTER= 'router';
    const CONF_NAME__LOGGER= 'logger';
    const CONF_NAME__SERVICES= 'services';

    private $_init_conf= [ // default params surcharged by __construct
        'conf_filetype'=> 'yaml',
        'app_controller_namespace'=> 'app\\controller\\',
        'app_service_namespace'=> 'app\\service\\',
    ];
    private $_app_confx; // container for app confs (lazy loaded)
    private $_core_confx; // container for core confs (lazy loaded)
    private $_confx;

    public function __construct( $_init_conf){
        $this->_init_conf= array_merge( $this->_init_conf, $_init_conf);
    }

    public function get_init_conf( $_key= null){
        if( ! isset( $_key)) return $this->_init_conf;
        return @$this->_init_conf[ $_key];
    }

    public function get_app_conf( $_name, $_keys= null, $_is_optional= false){
        if( ! isset( $this->_app_confx[ $_name])){
            $this->_prepare_conf( $_name, 'app');
        }
        return $this->_get_conf( 'app', $_name, $_keys, $_is_optional);
    }

    public function get_core_conf( $_name, $_keys= null, $_is_optional= false){
        if( ! isset( $this->_core_confx[ $_name])){
            $this->_prepare_conf( $_name, 'core');
        }
        return $this->_get_conf( 'core', $_name, $_keys, $_is_optional);
    }

    public function get_conf(  $_name, $_keys= null, $_is_optional= false){
         if( ! isset( $this->_core_confx[ $_name])){
            $this->_prepare_conf( $_name, 'core');
        }
        return $this->_get_conf( 'core', $_name, $_keys, $_is_optional);




    }

    private function _prepare_conf( $_name, $_type){
        $path= $_type == 'core' ? ND_PATH__FW_CONF : ND_PATH__APP_CONF;
        switch( $this->get_init_conf( 'conf_filetype')){
            case 'yaml':
                $conf_file= fh\get_local_file( $path . $_name . '.yml', false);
                $conf= $conf_file ? util\parse_yaml_file( $conf_file) : [];
                break;
            case 'json':
                $conf_file= fh\get_local_file( $path . $_name . '.json', false);
                $conf= $conf_file ? json_decode( $conf_file, true) : [];
                break;
            default:
                $conf= [];
                break;
        }
        if( $_type == 'core'){
            $this->_core_confx[ $_name]= $conf;
        } else {
            $this->_app_confx[ $_name]= $conf;
        }
    }

    /**
     * get a conf val according to an env type
     * @param array $_keys are conf keys
     * @param string $_env_type is among: dev, preprod, prod
     * @return NULL or conf val
     */
    private function _get( $_type, $_name, $_keys, $_env_name=null){
        $confx= $_type == 'core' ? $this->_core_confx : $this->_app_confx;
        $ret= $_env_name ? @$confx[ $_name][ $_env_name] : @$confx[ $_name];
        if( ! isset( $ret)) return;
        foreach( $_keys as $k){
            if( isset( $ret[ $k])){
                $ret= $ret[ $k];
            } else return;
        }
        return $ret;
    }

    /**
     * Retrouve une conf entière ou une partie de conf si un chemin de clés est fournie
     * @param type $_type
     * @param type $_name
     * @param type $_keys
     * @param type $_is_optional
     * @return type
     * @throws e\not_found_e
     */
    private function _get_conf( $_type, $_name, $_keys= null, $_is_optional= false){
        if( ! isset( $_name)) return null;
        if( ! isset( $_keys)){
            return $_type == 'core'
                ? $this->_core_confx[ $_name]
                : $this->_app_confx[ $_name];
        }
        foreach( [ null, ND_ENV_NAME, 'prod'] as $root_key){
            $v= $this->_get( $_type, $_name, $_keys, $root_key);
            if( isset( $v)) break;
        }
        if( ! $v && ! $_is_optional){
            $keyz= implode( ', ', $_keys);
            throw new e\not_found_e( 'No conf entry found for [%s]', [ $keyz]);
        }
        return $v;
    }

}
