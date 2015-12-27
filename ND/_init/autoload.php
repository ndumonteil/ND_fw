<?php
final class NDAutoloader {

    private $_dir2ns_x= [
        ND_PATH__FW_CORE=> [ 'ND', '_core'],
        ND_PATH__FW_MVC=> [ 'ND', '_mvc'],
        ND_PATH__FW_SERVICE=> [ 'ND', 'service'],
        ND_PATH__FW_TOOL=> [ 'ND', 'tool'],
        ND_PATH__APP=> [ 'app', 'controller'],
        ND_PATH__APP_SERVICE=> [ 'app', 'service'],
        ND_PATH__APP_MODEL_DRO=> [ 'app', 'model', 'dro'],
        ND_PATH__APP_MODEL_REPO=> [ 'app', 'model', 'repo'],
        ND_PATH__APP_TOOL=> [ 'app', 'tool'],
    ];

    public function __construct() {
        spl_autoload_register([ $this, '_search_class']);
    }

    private function _search_class( $_class){
        $class_ns= explode( '\\', $_class);
        $class_name= array_pop( $class_ns);
        foreach( $this->_dir2ns_x as $dir=> $ns_path){
            if( $ns_path === $class_ns){
                $this->_search_in_dir( $class_name, $dir);
                return;
            }
        }
    }

    private function _search_in_dir( $_class, $_dir){
        foreach( scandir( $_dir ) as $file){
            if( is_dir( $_dir . $file ) && substr( $file, 0, 1) !== '.'){
                if( $this->_search_in_dir( $_class, $_dir . $file . DIRECTORY_SEPARATOR)){
                    return true;
                }
            }
            if( substr( $file, 0, 2) !== '._'
                && preg_match( "/.php$/i" , $file)
                && str_replace( '.php', '', $file) == strtolower( $_class))
            {
                include $_dir . $file;
                return true;
            }
        }
    }
}

$autoloader = new NDAutoloader();
