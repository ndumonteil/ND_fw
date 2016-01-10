<?php
final class NDAutoloader {

    private $_dir2ns_x= [
        ND_PATH__FW_CORE=> [ 'ND', 'core'],
        ND_PATH__FW_CORE_CONNEXION=> [ 'ND', 'core', 'connexion'],
        ND_PATH__FW_CORE_PDO=> [ 'ND', 'core', 'PDO'],
        ND_PATH__FW_MVC=> [ 'ND', 'MVC'],
        ND_PATH__FW_SERVICE=> [ 'ND', 'service'],
        ND_PATH__FW_TOOL=> [ 'ND', 'tool'],
        ND_PATH__APP=> [ 'app', 'controller'],
        ND_PATH__APP_SERVICE=> [ 'app', 'service'],
        ND_PATH__APP_MODEL_MDO=> [ 'app', 'model', 'MDO'],
        ND_PATH__APP_MODEL_ENTITY=> [ 'app', 'model', 'entity'],
        ND_PATH__APP_TOOL=> [ 'app', 'tool'],
    ];

    public function __construct() {
        spl_autoload_register([ $this, '_search_class']);
    }

    private function _search_class( $_class){
        $class_ns= explode( '\\', $_class);
        $class_name= array_pop( $class_ns);
        foreach( $this->_dir2ns_x as $dir=> $ns){
            if( $ns === $class_ns){
                $this->_search_in_dir( $class_name, $dir);
                return;
            }
        }
    }

    private function _search_in_dir( $_class_name, $_dir){
        $subdirs= [];
        foreach( scandir( $_dir) as $file){
            if( is_dir( $_dir . $file)){
                if( substr( $file, 0, 1) == '.'){ continue;}
                $subdirs[]= $_dir . $file . DIRECTORY_SEPARATOR;
            }
            if( substr( $file, 0, 2) != '._' && preg_match( "/.php$/i" , $file)
                && str_replace( '.php', '', $file) == strtolower( $_class_name)
            ){
                include $_dir . $file;
                return true;
            }
        }
        foreach( $subdirs as $dir){
            if( $this->_search_in_dir( $_class_name, $dir)){
                return true;
            }
        }
    }
    
}

$autoloader = new NDAutoloader();
