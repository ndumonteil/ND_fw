<?php
namespace ND\core\service;

use ND\exception as e;
use ND\core\tool\fh;
use ND\core\tool\util;

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
    private $_confx; // container for app confs (lazy loaded)

    public function __construct( $_init_conf){
        $this->_init_conf= array_merge( $this->_init_conf, $_init_conf);
    }

    public function get_init_conf( $_key= null){
        if( ! isset( $_key)) return $this->_init_conf;
        return @$this->_init_conf[ $_key];
    }

    public function get( $_name, $_keys= null, $_is_optional= false){
        if( ! isset( $this->_confx[ $_name])){
            $this->_prepare_conf( $_name);
        }
        return $this->_get_conf( $_name, $_keys, $_is_optional);
    }

    private function _prepare_conf( $_name){
        $filetype= $this->get_init_conf( 'conf_filetype');
        $core_conf_file= fh::get_local_file( ND_PATH__FW_CONF . $_name . '.' . $filetype, false);
        $app_conf_file= fh::get_local_file( ND_PATH__APP_CONF . $_name . '.' . $filetype, false);
        switch( $filetype){
            case 'yaml':
                $core_conf= $core_conf_file ? util::parse_yaml_file( $core_conf_file) : [];
                $app_conf= $app_conf_file ? util::parse_yaml_file( $app_conf_file) : [];
                break;
            case 'json':
                $core_conf= $core_conf_file ? json_decode( $core_conf_file, true) : [];
                $app_conf= $app_conf_file ? json_decode( $app_conf_file, true) : [];
                break;
            default:
                $core_conf= $app_conf= [];
                break;
        }
        if( ! empty( $core_conf) && !empty( $app_conf)){
            $conf= $this->_merge_confs( $core_conf, $app_conf);
        } elseif( ! empty( $core_conf)){
            $conf= $core_conf;
        } else {
            $conf= $app_conf;
        }
        $this->_confx[ $_name]= $conf;
    }

    private function _merge_confs( $_core_conf, $_app_conf){
        $conf= [];
        $keys= array_merge( array_flip( array_keys( $_core_conf)), array_flip( array_keys( $_app_conf)));
        foreach( $keys as $key=> $null){
            $conf[ $key]= ( @$_core_conf[ $key] ?: []) + ( @$_app_conf[ $key] ?: []);
        }
        return $conf;
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
    private function _get_conf( $_name, $_keys= null, $_is_optional= false){
        if( ! isset( $_name)) return null;
        $conf= @$this->_confx[ $_name];
        if( ! $conf){
            return null;
        }
        if( ! isset( $_keys)){
            return $conf;
        }
        foreach( [ null, ND_ENV_NAME, 'prod'] as $root_key){
            $v= $this->_get( $conf, $_keys, $root_key);
            if( isset( $v)) break;
        }
        if( ! isset( $v) && ! $_is_optional){
            $keyz= implode( ', ', $_keys);
            throw new e\not_found_e( 'No conf entry found for [%s]', [ $keyz]);
        }
        return $v;
    }

    /**
     * get a conf val according to an env type
     * @param array $_keys are conf keys
     * @param string $_env_type is among: dev, preprod, prod
     * @return NULL or conf val
     */
    private function _get( $_conf, $_keys, $_env_name=null){
        $ret= $_env_name ? @$_conf[ $_env_name] : $_conf;
        if( ! isset( $ret)) return;
        foreach( $_keys as $k){
            if( isset( $ret[ $k])){
                $ret= $ret[ $k];
            } else return;
        }
        return $ret;
    }

}
